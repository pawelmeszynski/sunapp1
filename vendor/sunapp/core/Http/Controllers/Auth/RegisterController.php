<?php

namespace SunAppModules\Core\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use SunApp\Http\Controllers\Controller as BaseController;
use User;

class RegisterController extends BaseController
{
    use RegistersUsers {
        register as registration;
    }

    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->redirectTo = route('SunApp::home');
        $this->middleware('guest');
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        return theme_view('auth.register');
    }

    public function stepOneRegistration(Request $request)
    {
        $registration_data = $request->all();

        if ($this->validator($registration_data)->fails()) {
            return redirect()->back()->withInput()->withErrors($this->validator($registration_data)->errors());
        }

        $google2fa = app('pragmarx.google2fa');

        $registration_data['google2fa_secret'] = $google2fa->generateSecretKey();

        $request->session()->put('registration_data', $registration_data);

        $QR_Image = $google2fa->getQRCodeInline(
            config('app.name'),
            $registration_data['email'],
            $registration_data['google2fa_secret']
        );

        return theme_view('google2fa.register', [
            'QR_Image' => $QR_Image, 'secret' => $registration_data['google2fa_secret']
        ]);
    }

    public function completeRegistration(Request $request)
    {
        $request->merge($request->session()->get('registration_data'));

        return $this->registration($request);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $validator = $this->getValidationFactory()
            ->make($data, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:6', 'confirmed']
            ]);
        return $validator;
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $google2fa = app('pragmarx.google2fa');
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => 1,
            'is_ldap' => 0,
            'ldap_name' => '',
            'google2fa_secret' => $google2fa->generateSecretKey(),
            'is2fa_google_enabled' =>  true
        ]);

        session()->forget('registration_data');

        return $user;
    }
}
