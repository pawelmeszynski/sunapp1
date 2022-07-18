<?php

namespace SunAppModules\Core\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Repository;
use SunAppModules\Core\Forms\UserForm;
use User;

class TwoFactorAuthGoogleController extends \Controller
{
    protected $prefix = 'core::google2fa';
    protected $class = User::class;
    protected $formClass = UserForm::class;

    public function __construct(Repository $repository)
    {
    }

    public function index(Request $request)
    {
        return theme_view(
            $this->prefix . '.index'
        );
    }

    public function reauthenticate2fa(Request $request)
    {
        $auth = Auth::user();

        $google2fa = app('pragmarx.google2fa');

        //showing user acutal qr code
        $QR_Image = $auth->getQrCode2faGoogle();

        return theme_view(
            'core::google2fa.reauthenticate',
            [
                'is_enable' => $auth->getIs2faGoogleEnabled(),
                'is_verified' => $auth->getVerifiedAt2faGoogle(),
                'QR_Image' => $QR_Image,
                'secret' => $auth->google2fa_secret
            ]
        );
    }

    public function enable2fa(Request $request)
    {
        $auth = Auth::user();

        $data = $request->all();
        $status = null;

        //checking if user hasn't google 2fa
        if (is_null($auth->getIs2faGoogleEnabled())) {
            $google2fa = app('pragmarx.google2fa');
            $auth->update([
                'google2fa_secret' =>  $google2fa->generateSecretKey(),
                'is2fa_google_enabled' =>  true
            ]);
            return \Redirect::back();
        }

        if ($data['is_enabled'] == true) {
            //disabled 2fa for logged user
            $status = false;
        } else {
            $status = true;
        }

        $changes = $auth->update([
            'is2fa_google_enabled' => $status
        ]);

        return \Redirect::back();
    }

    public function verify2fa(Request $request)
    {
        $auth = Auth::user();
        $data = $request->all();

        $google2fa = app('pragmarx.google2fa');
        $valid = $google2fa->verifyKey($auth->google2fa_secret, $data['verify_code']);

        if ($valid === true) {
            $auth->update([
                'verified_at_2fa_google' => date('Y-m-d H:i:s'),
            ]);
            return \Redirect::back();
        }

        return \Redirect::back()->withErrors([trans('core::google2fa.verification_code_invalid')]);
    }

    public function generateForAll2fa($model)
    {
        // app("App\\$modelo")
        $users = app($model)::whereNull('google2fa_secret')->get();
        $google2fa = app('pragmarx.google2fa');
        $i = 0;
        foreach ($users as $user) {
            $user->update([
                'google2fa_secret' => $google2fa->generateSecretKey(),
                'is2fa_google_enabled' => true
            ]);
            $i++;
        }
        return trans('core::google2fa.generate_for_all', ['i' => $i]);
    }
}
