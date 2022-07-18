<?php

namespace SunAppModules\Core\Http\Controllers;

use Auth;
use Repository;
use User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use SunAppModules\Core\Forms\MyAccountForm;

class MyAccountController extends Controller
{
    protected $prefix = 'core::my-account';
    protected $class = User::class;
    protected $formClass = MyAccountForm::class;

    private $user;

    public function __construct(Repository $repository)
    {
        $this->item = new $this->class();
        $this->items = $this->class::query();
        $this->repository = $repository;
        $this->repository->setModel($this->class);

        $this->user = Auth::user();
    }

    public function edit($id, Request $request)
    {
        $this->item = $this->item->findOrFail($id);

        if ($this->user->id !== (int) $id) {
            abort(403, trans('core::messages.cant_edit_not_yours_account'));
        }

        if ($request->ajax()) {
            return $this->repository->find($id);
        }

        $this->prepareForm();
        $this->itemForm->setupModel($this->item);

        return theme_view(
            $this->prefix . '.edit',
            [
                'form' => $this->itemForm,
                'item' => $this->item
            ]
        );
    }

    public function update(Request $request, $id)
    {
        $this->item = $this->item->findOrFail($id);

        if ($this->user->id !== (int) $id) {
            abort(403, trans('core::messages.cant_edit_not_yours_account'));
        }

        $this->prepareForm();
        $this->itemForm->redirectIfNotValid();

        if (isset($request->api_token) && $request->api_token != '') {
            $request->merge(['api_token' => hash('sha256', $request->api_token)]);
        }

        if ($this->item->is_ldap == 0) {
            if ($request->get('password') == '') {
                $this->item->update(array_merge($request->except('password'), [
                    'is_ldap' => 0,
                    'ldap_name' => ''
                ]));
            } else {
                $this->itemForm->validate(['password' => 'required|same:password_confirmation|min:6']);
                $this->itemForm->redirectIfNotValid();
                $this->item->update(array_merge(
                    $request->all(),
                    ['is_ldap' => 0, 'ldap_name' => '', 'password' => Hash::make($request->password)]
                ));
            }
        } else {
            $this->item->update(array_merge($request->except('name', 'password')));
        }

        return redirect()->back()->withMessage(
            'success',
            trans('core::actions.update_success'),
            200,
            $this->repository->parserResult($this->item->fresh())
        );
    }
}
