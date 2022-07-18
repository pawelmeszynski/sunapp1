<?php

namespace SunAppModules\SunBet\Http\Controllers\Auth;

use SunAppModules\Core\Http\Controllers\Controller;
use Bouncer;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use SunApp\Http\Controllers\Controller as BaseController;
use SunAppModules\SunBet\Entities\SunbetUser;
use User;
use UserGroup;
use Laravel\Passport\Http\Controllers\AccessTokenController;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/sunbet/login';


    public function __construct()
    {
        $this->redirectTo = route('SunApp::home');
        $this->middleware('guest')->except('logout', 'loginAs');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function loginUser(Request $request)
    {
        if ($request->remember_device == "on") {
            $request->session()->put('remember_device', true);
        }
        $this->validateLogin($request);

        if (
            method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)
        ) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }


        if (strpos($request->email, 'sungroup.pl') !== false) {
            if ($ldapData = $this->attemptLdap($request)) {
                switch ($ldapData['code']) {
                    case 200:
                        return $this->attemptWithLdap($request, $ldapData['data']);
                        break;
                    default:
                        return $this->attemptWithoutLdap($request);
                        break;
                }
            }
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }
    public function attemptLdap($request)
    {
        $token = 'gygUUE8u5NDHRapTSLdDRfaQDLAXVV9pATtsgB5pg'
            . '8d9WK6YU45Axt8M888C568GpMHgF3b8wDTqNJbXDhXKjuaVtRqysHdxQ7wr59ArGJAZJ6Bw8vvLnsvavbZwkZD7';
        //ToDo:: Jakiś dobry sposób na podawanie dokładnego linku za jednym ruchem;
        $ldapClient = new Client([
            'base_uri' => env('LDAP_DOMAIN', 'https://biuro.sungroup.pl/'),
            'http_errors' => false
        ]);
        $data = $request->all();
        $data['sys_user'] = $request->server('USER');
        $data['group'] = 'L_SUNAPP';
        try {
            $response = $ldapClient->request('POST', 'auth/ldap.php', [
                'headers' => ['Authorization' => 'Bearer ' . $token],
                'form_params' => $data
            ]);
        } catch (\Exception $e) {
            return ['code' => 500, 'data' => null];
        }
        return ['code' => $response->getStatusCode(), 'data' => json_decode($response->getBody()->getContents())];
    }

    public function attemptWithLdap($request, $ldapData)
    {
//        dd($request);
        $user = SunbetUser::firstOrNew([
            'email' => $ldapData->email
        ]);
        $user->forceFill([
            'name' => $ldapData->name,
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make($request->password),
            'is_ldap' => 1,
            'ldap_name' => $ldapData->ldap_name
        ])->save();
        $attempt = $this->guard()->attempt(
            $this->credentials($request),
            $request->filled('remember')
        );
        $user->groups()->syncWithoutDetaching(
            UserGroup::where('name', 'SunGroup')->where('core', 1)->first()
        );

        Bouncer::allow($user)->everything();
        return $this->sendLoginResponse($request);
    }

    public function attemptWithoutLdap($request)
    {
        $attempt = $this->guard()->attempt(
            $this->credentials($request),
            $request->filled('remember')
        );
        if ($attempt) {
            $user = $this->guard()->user();
            $accessToken = $this->issueToken();
            if ($user->is_ldap) {
                if ($user->logged_at > Carbon::now()->subDays(3)) {
                    return $this->sendLoginResponse($request);
                }
            } else {
                return $this->sendLoginResponse($request)->issueToken();
            }
            $this->guard()->logout();
            $this->incrementLoginAttempts($request);
            throw ValidationException::withMessages([
                $this->username() => [trans('auth.ldap_failed')],
            ]);
        }
        return $this->sendFailedLoginResponse($request);
    }
}
