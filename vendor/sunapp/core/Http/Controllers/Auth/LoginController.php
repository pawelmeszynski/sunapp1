<?php

namespace SunAppModules\Core\Http\Controllers\Auth;

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
use User;
use UserGroup;

class LoginController extends BaseController
{
    use AuthenticatesUsers;

    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    /**
     * Where to redirect users after login.
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
        $this->middleware('guest')->except('logout', 'loginAs');
    }

    /**
     * Show the application's login form.
     *
     * @return Response
     */
    public function showLoginForm()
    {
        return theme_view('auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  Request  $request
     * @return RedirectResponse|Response|JsonResponse
     *
     * @throws ValidationException
     */
    public function login(Request $request)
    {
        if ($request->remember_device == "on") {
            $request->session()->put('remember_device', true);
        }
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
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
        $user = User::firstOrNew([
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
            if ($user->is_ldap) {
                if ($user->logged_at > Carbon::now()->subDays(3)) {
                    return $this->sendLoginResponse($request);
                }
            } else {
                return $this->sendLoginResponse($request);
            }
            $this->guard()->logout();
            $this->incrementLoginAttempts($request);
            throw ValidationException::withMessages([
                $this->username() => [trans('auth.ldap_failed')],
            ]);
        }
        return $this->sendFailedLoginResponse($request);
    }

    /**
     * The user has been authenticated.
     *
     * @param  Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        $user->forceFill([
            'logged_at' => Carbon::now(),
        ])->save();
    }

    protected function loggedOut(Request $request)
    {
        return redirect($this->redirectTo);
    }

    public function loginAs(Request $request, $to_id = false, $from_id = false, $token = false)
    {
        if (auth()->loginAs(User::class, $to_id, $from_id, $token)) {
            return redirect($this->redirectTo);
        }
    }
}
