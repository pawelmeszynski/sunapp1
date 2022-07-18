<?php

namespace SunAppModules\Core\Http\Controllers;

use Auth;
use Bouncer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Repository;
use SunAppModules\Core\Forms\UserForm;
use SunAppModules\Core\Entities\User;

class UsersController extends Controller
{
    protected $prefix = 'core::users';
    protected $class = User::class;
    protected $formClass = UserForm::class;

    public function __construct(Repository $repository)
    {
        $this->item = new $this->class();
        $this->items = $this->class::query();
        $this->repository = $repository;
        $this->repository->setModel($this->class)->setSearchable([
            'name' => 'like',
            'email' => 'like',
            'ldap_name' => 'like',
            'email_verified_at',
            'banned',
            'superadmin',
            'ldap'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $item = new User();
        $form = $this->form(UserForm::class, [
            'model' => $item
        ]);
        $form->validate(['password' => 'required|same:password_confirmation|min:6']);

        $form->redirectIfNotValid();

        if (isset($request->api_token) && $request->api_token != '') {
            $request->merge(['api_token' => hash('sha256', $request->api_token)]);
        }

        $item = User::create(array_merge(
            $request->all(),
            ['is_ldap' => 0, 'ldap_name' => '', 'password' => Hash::make($request->password)]
        ));

        if ($request->has('user_group')) {
            $item->groups()->sync($request->get('user_group'));
        }

        if ($request->has('user_role')) {
            $item->roles()->sync($request->get('user_role'));
        }

        if ($request->email_verify) {
            $item->forceFill(['email_verified_at' => Carbon::now()])->save();
        }
        return redirect()->back()->withMessage(
            'success',
            trans('core::actions.update_success'),
            200,
            $this->repository->parserResult($item->fresh())
        );
    }

    /**
     * Update the specified resource in storage.
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $item = User::withTrashed()->find($id);
        if ($request->get('restore') == true) {
            $item->restore();

            return redirect()->back()->withMessage(
                'success',
                trans('core::actions.restore_success'),
                200,
                $this->repository->parserResult($item->fresh())
            );
        }
        $form = $this->form(UserForm::class, [
            'model' => $item
        ]);
        $form->redirectIfNotValid();

        if (isset($request->api_token) && $request->api_token != '') {
            $request->merge(['api_token' => hash('sha256', $request->api_token)]);
        }

        if ($item->is_ldap == 0) {
            if ($request->get('password') == '') {
                $item->update(array_merge($request->except('password'), ['is_ldap' => 0, 'ldap_name' => '']));
            } else {
                $form->validate(['password' => 'required|same:password_confirmation|min:6']);
                $form->redirectIfNotValid();
                $item->update(array_merge(
                    $request->all(),
                    ['is_ldap' => 0, 'ldap_name' => '', 'password' => Hash::make($request->password)]
                ));
            }
        } else {
            if ($request->get('password') == '') {
                $item->update(array_merge($request->except('password')));
            } else {
                $item->update(array_merge($request->all(), ['password' => Hash::make($request->password)]));
            }
        }

        if ($request->has('user_group')) {
            $item->groups()->sync($request->get('user_group'));
        }

        if ($request->has('user_role')) {
            $item->roles()->sync($request->get('user_role'));
        }

        if ($request->email_verify) {
            $item->forceFill(['email_verified_at' => Carbon::now()])->save();
        }
        return redirect()->back()->withMessage(
            'success',
            trans('core::actions.update_success'),
            200,
            $this->repository->parserResult($item->fresh())
        );
    }

    /**
     * Remove the specified resource from storage.
     * @param  int  $id
     * @return Response
     */
    public function destroy($id, Request $request)
    {
        $item = User::withTrashed()->find($id);
        if ($item->id === Auth::user()->id) {
            abort(403, trans('core::messages.cant_remove_yourself'));
        }
        if ($item->is_ldap == 1) {
            abort(403, trans('core::messages.cant_remove_ldap_user'));
        }
        if ($request->get('force', 0) == true) {
            $item->forceDelete();
            return redirect()->back()->withMessage('success', trans('core::actions.force_destroy_success'));
        }
        $item->delete();
        return redirect()->back()->withMessage('success', trans('core::actions.destroy_success'));
    }

    public function super($id, Request $request)
    {
        if (Bouncer::cannot('make-superadmins')) {
            abort(403);
        }
        $user = User::find($id);
        if (isset($request->superadmin) && $request->superadmin == 0) {
            Bouncer::disallow($user)->everything();
            return redirect()->back()->withMessage('success', trans('core::users.superadmin_not_ok'));
        }
        Bouncer::allow($user)->everything();
        return redirect()->back()->withMessage('success', trans('core::users.superadmin_ok'));
    }

    public function ban($id, Request $request)
    {
        $user = User::find($id);
        if (!Bouncer::can('edit', $user)) {
            abort(403);
        }
        if (isset($request->banned) && $request->banned == 0) {
            Bouncer::unforbid($user)->everything();
            return redirect()->back()->withMessage('success', trans('core::users.unbanned'));
        }
        Bouncer::forbid($user)->everything();
        return redirect()->back()->withMessage('success', trans('core::users.was_banned'));
    }

    /**
     * Login admin as selected user
     * @param  int  $id
     * @param  Request  $request
     * @return Response
     * @throws Exception
     */
    public function loginAs($to_id, Request $request)
    {
        $item = User::withTrashed()->findOrFail($to_id);
        if (!Bouncer::can('edit', $item)) {
            abort(403);
        }
        $data = auth()->loginAs(User::class, $to_id);
        return redirect()->route('SunApp::login_as', $data);
    }

    public function enable2fa($id, Request $request)
    {
        $data = $request->all();
        $status = null;
        $trans = null;

        $user = User::find($id);
        if (!Bouncer::can('edit', $user)) {
            abort(403);
        }

        //checking if user hasn't google 2fa
        if (is_null($user->getIs2faGoogleEnabled())) {
            $google2fa = app('pragmarx.google2fa');
            $user->update([
                'google2fa_secret' =>  $google2fa->generateSecretKey(),
                'is2fa_google_enabled' =>  true
            ]);
            return \Redirect::back();
        }

        if ($data['is_enabled'] == true) {
            //disabled 2fa for logged user
            $status = false;
            $trans = trans('core::users.was_disable2fa');
        } else {
            $status = true;
            $trans = trans('core::users.was_enable2fa');
        }

        $changes = $user->update([
            'is2fa_google_enabled' => $status
        ]);

        return redirect()->back()->withMessage('success', $trans);
    }

    public function reset2fa($id, Request $request)
    {
        $user = User::find($id);
        if (!Bouncer::can('edit', $user)) {
            abort(403);
        }

        $google2fa = app('pragmarx.google2fa');
        $user->update([
            'google2fa_secret' =>  $google2fa->generateSecretKey(),
            'verified_at_2fa_google' =>  null,
            'is2fa_google_enabled' =>  true
        ]);

        return redirect()->back()->withMessage('success', trans('core::users.was_reset2fa'));
    }
}
