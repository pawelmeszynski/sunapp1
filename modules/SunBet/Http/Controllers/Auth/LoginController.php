<?php

namespace SunAppModules\SunBet\Http\Controllers\Auth;

use Bouncer;
use Carbon\Carbon;
use DB;
use GuzzleHttp\Client;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Str;
use SunApp\Http\Controllers\Controller as BaseController;
use SunAppModules\SunBet\Entities\SunbetUser;
use URL;
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
        return theme_view('sunbet::auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param Request $request
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
//
        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (
            property_exists($this, 'hasTooManyLoginAttempts') &&
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
        // to login and redirect the users back to the login form. Of course, when this
        // users surpasses their maximum number of attempts they will get locked out.
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
        $ldapUser = SunbetUser::updateOrCreate(
            [
                'email' => $ldapData->email,
            ],
            [
                'name' => $ldapData->name ?? null,
                'password' => Hash::make($request->password),
                'is_ldap' => 1,
                'ldap_name' => $ldapData->ldap_name
            ]);
//dd($attempt = $this->guard()->attempt(
//    $this->credentials($request),
//    $request->filled('remember')
//));
//        $attempt = $this->guard()->attempt(
//            $this->credentials($request),
//            $request->filled('remember')
//        );
//        $ldapUser->groups()->syncWithoutDetaching(
//            UserGroup::where('name', 'SunGroup')->where('core', 1)->first()
//        );

        $client = new Client();
        $response = json_decode($client->request('POST', 'https://sunpame-sunapp.test.sungroup.pl/oauth/token', [
            'form_params' => [
                'grant_type' => env('SUNBET_GRANT_TYPE'),
                'client_id' => env('SUNBET_ID'),
                'client_secret' => env('SUNBET_SECRET'),
                'username' => $ldapUser->email,
                'password' => $request->password
            ],
        ])->getBody()->getContents());

        $accessToken = $response->access_token;

        $request->query->add(['access_token' => $accessToken]);

        $request->attributes->add(['access_token' => $accessToken]);

        $request = $this->authorizatedUserResponse($request)->with(['access_token' => $request->access_token]);

        return $request;
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
                $newUser = SunbetUser::updateOrCreate(
                    [
                        'email' => $request->email,
                    ],
                    [
                        'name' => $request->name ?? null,
                        'password' => Hash::make($request->password),
                        'logged_at' => Carbon::now(),
                    ]);
                $client = new Client();
                $response = json_decode(
                    $client->request('POST', 'https://sunpame-sunapp.test.sungroup.pl/oauth/token', [
                        'form_params' => [
                            'grant_type' => env('SUNBET_GRANT_TYPE'),
                            'client_id' => env('SUNBET_ID'),
                            'client_secret' => env('SUNBET_SECRET'),
                            'username' => $newUser->email,
                            'password' => $request->password
                        ],
                    ])->getBody()->getContents());
                $accessToken = $response->access_token;
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
     * The users has been authenticated.
     *
     * @param Request $request
     * @param mixed $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        $cred = $request->validate([
            'email' => 'required|email|exists:sunbet_users',
            'password' => 'required'
        ]);
        if ($cred) {
            $user = SunbetUser::updateOrCreate([
                'email' => $request->email,
            ],
                [
                    'logged_at' => Carbon::now(),
                ]);
        }
    }

    protected function loggedOut(Request $request)
    {
        $user = SunbetUser::updateOrCreate([
            'email' => $request->email,
        ],
            [
                'logged_at' => null,
            ]);
        return redirect($this->redirectTo);
    }

    public function loginAs(Request $request, $to_id = false, $from_id = false, $token = false)
    {
        if (auth()->loginAs(User::class, $to_id, $from_id, $token)) {
            return redirect($this->redirectTo);
        }
    }

}
