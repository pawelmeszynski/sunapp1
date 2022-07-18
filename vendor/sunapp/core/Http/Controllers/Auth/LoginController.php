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
use Str;
use SunApp\Http\Controllers\Controller as BaseController;
use SunAppModules\SunBet\Entities\SunbetUser;
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
     * The access token.
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
//        dd($ldapData = $this->attemptLdap($request));
//            if (strpos($request->email, 'sungroup.pl') !== false) {
//                if ($ldapData = $this->attemptLdap($request)) {
//                    dump('dupa');
//                    $client = new Client();
//                    $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjM5MWQ2ZWE4NGY1YzM4ZDg4YjBiZTBkOTY4YzJiNDhiMjQzZTlmNjZkMzRkZDdkYWFmM2YyMmIwNzRlZjQwNmY0Y2Q3YTA5NTBhNTYxNWNkIn0.eyJhdWQiOiIyIiwianRpIjoiMzkxZDZlYTg0ZjVjMzhkODhiMGJlMGQ5NjhjMmI0OGIyNDNlOWY2NmQzNGRkN2RhYWYzZjIyYjA3NGVmNDA2ZjRjZDdhMDk1MGE1NjE1Y2QiLCJpYXQiOjE2NTc4OTIwNTksIm5iZiI6MTY1Nzg5MjA1OSwiZXhwIjoxNjU4NTAyMzA0LCJzdWIiOiI0Iiwic2NvcGVzIjpbXX0.vEqb4NXs075hLJeur-IqEp838tysOU1CpZ93qxtVtpo6GsHdQCsRJkmZbNb1scWja7FfAf3aiSOOmSQVwDlzVX_tU2TdZ6OD85hKG_w_ZjWCRnuBB5TiJdXCZmcRlJQ-BuJTnBAl5ScqIAcBifS9chRuTM-AqviOU9HyBkzbgJsZSb9wIigUMx9TIu1rjAg6wXvRBnNRtu6iwsy-fcLcW8PcaHk4X6Y_Y9whiG9eBKywd9AkrGxnYmgzUpyeR6R92zIf4ao07qH9Om2KtJimubyoneJIASR281X-kpmrgTb0wGvpmOFT9_0ZpFUFCQIAcEIdepWVjjIvoiff4X3CXeMHhSFkvxCctenaNFiz859c-Vc-mUo3Po-G9x3KY1a8-0tfhpOv6tOFzD0Hl_dyKWcCEUJjiGgSgTCZWxC1_g5kjVE175jP-3ECzrQkOFjxQsM7BPY1vejUgr7I5qA2PVjGgo6ekt5632dVxCEaz4XjvUrhcR09k52LLTGlGGi-AuMbh4QT-ZNIDBeZJ3Ptd1YdWbKAwuixBYZW9iTv-JJC-rawapbMGC1xO7WC7AEUprjUZvTP4nVgnfa3QOA5RLCnmf15tgXk2HLVOaKEFgfdpoX8z-Vg_GzinnYssPKq2PU5XVmoT7oL1Yr55HQFJp3ZuZ4RsLfXGfIM_--JuG0';
//                    $response = json_decode($client->request('POST', 'https://sunapp1.ddev.site/oauth/token', [
//                        'headers' => [
//                            'Authorization' => 'Bearer ' . $token],
//                        'form_params' => [
//                            'grant_type' => 'password',
//                            'client_id' => '2',
//                            'client_secret' => 'UJyiUsDkhMHRgGlMGADVSkIJAOE44Lr0OAKl6dJU',
//                            'username' => '123@123.com',
//                            'password' => '123'
//                        ],
//                    ])->getBody()->getContents());
//                    $accessToken = $response->access_token;
//
//                    return $this->attemptWithLdap($request, $ldapData['data']);
//                } else {
//                    dump('1111111111111111111111111');
//                    $response = json_decode($client->request('POST', 'https://sunapp1.ddev.site/oauth/token', [
//                        'headers' => [
//                            'Authorization' => 'Bearer ' . $token],
//                        'form_params' => [
//                            'grant_type' => 'password',
//                            'client_id' => '2',
//                            'client_secret' => 'UJyiUsDkhMHRgGlMGADVSkIJAOE44Lr0OAKl6dJU',
//                            'username' => '123@123.com',
//                            'password' => '123'
//                        ],
//                    ])->getBody()->getContents());
//                    $accessToken = $response->access_token;
//
//                    return $this->attemptWithoutLdap($request);
//                }
//            }
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

        $ldapUser = SunbetUser::updateOrCreate(
            [
                'email' => $ldapData->email,
            ],
            [
                'name' => $ldapData->name ?? null,
                'password' => Hash::make($ldapData->password),
                'is_ldap' => 1,
                'ldap_name' => $ldapData->ldap_name
            ]);

        $attempt = $this->guard()->attempt(
            $this->credentials($request),
            $request->filled('remember')
        );
        $ldapUser->groups()->syncWithoutDetaching(
            UserGroup::where('name', 'SunGroup')->where('core', 1)->first()
        );
        Bouncer::allow($ldapUser)->everything();
        $client = new Client();
        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjM5MWQ2ZWE4NGY1YzM4ZDg4YjBiZTBkOTY4YzJiNDhiMjQzZTlmNjZkMzRkZDdkYWFmM2YyMmIwNzRlZjQwNmY0Y2Q3YTA5NTBhNTYxNWNkIn0.eyJhdWQiOiIyIiwianRpIjoiMzkxZDZlYTg0ZjVjMzhkODhiMGJlMGQ5NjhjMmI0OGIyNDNlOWY2NmQzNGRkN2RhYWYzZjIyYjA3NGVmNDA2ZjRjZDdhMDk1MGE1NjE1Y2QiLCJpYXQiOjE2NTc4OTIwNTksIm5iZiI6MTY1Nzg5MjA1OSwiZXhwIjoxNjU4NTAyMzA0LCJzdWIiOiI0Iiwic2NvcGVzIjpbXX0.vEqb4NXs075hLJeur-IqEp838tysOU1CpZ93qxtVtpo6GsHdQCsRJkmZbNb1scWja7FfAf3aiSOOmSQVwDlzVX_tU2TdZ6OD85hKG_w_ZjWCRnuBB5TiJdXCZmcRlJQ-BuJTnBAl5ScqIAcBifS9chRuTM-AqviOU9HyBkzbgJsZSb9wIigUMx9TIu1rjAg6wXvRBnNRtu6iwsy-fcLcW8PcaHk4X6Y_Y9whiG9eBKywd9AkrGxnYmgzUpyeR6R92zIf4ao07qH9Om2KtJimubyoneJIASR281X-kpmrgTb0wGvpmOFT9_0ZpFUFCQIAcEIdepWVjjIvoiff4X3CXeMHhSFkvxCctenaNFiz859c-Vc-mUo3Po-G9x3KY1a8-0tfhpOv6tOFzD0Hl_dyKWcCEUJjiGgSgTCZWxC1_g5kjVE175jP-3ECzrQkOFjxQsM7BPY1vejUgr7I5qA2PVjGgo6ekt5632dVxCEaz4XjvUrhcR09k52LLTGlGGi-AuMbh4QT-ZNIDBeZJ3Ptd1YdWbKAwuixBYZW9iTv-JJC-rawapbMGC1xO7WC7AEUprjUZvTP4nVgnfa3QOA5RLCnmf15tgXk2HLVOaKEFgfdpoX8z-Vg_GzinnYssPKq2PU5XVmoT7oL1Yr55HQFJp3ZuZ4RsLfXGfIM_--JuG0';
        $response = json_decode($client->request('POST', 'https://sunapp1.ddev.site/oauth/token', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token ],
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => '2',
                'client_secret' => 'UJyiUsDkhMHRgGlMGADVSkIJAOE44Lr0OAKl6dJU',
                'username' => '123@123.com',
                'password' => '123'
            ],
        ])->getBody()->getContents());
        $accessToken = $response->access_token;
        return $this->sendLoginResponse($request);
    }

    public function attemptWithoutLdap($request)
    {
        $attempt = $this->guard()->attempt(
            $this->credentials($request),
            $request->filled('remember')
        );
        if ($request) {
            $user = $this->guard()->user();
            if ($user->is_ldap) {
                if ($user->logged_at > Carbon::now()->subDays(3)) {
                    return $this->sendLoginResponse($request);
                }
            } else {
                $newUser = SunbetUser::updateOrCreate(
                    [
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    ],
                    [
                    'ldap_name' => $request->ldap_name ?? null,
                    'name' => $request->name ?? 'nic',
                    'logged_at' => Carbon::now(),
                ]);
                $client = new Client();
                $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjM5MWQ2ZWE4NGY1YzM4ZDg4YjBiZTBkOTY4YzJiNDhiMjQzZTlmNjZkMzRkZDdkYWFmM2YyMmIwNzRlZjQwNmY0Y2Q3YTA5NTBhNTYxNWNkIn0.eyJhdWQiOiIyIiwianRpIjoiMzkxZDZlYTg0ZjVjMzhkODhiMGJlMGQ5NjhjMmI0OGIyNDNlOWY2NmQzNGRkN2RhYWYzZjIyYjA3NGVmNDA2ZjRjZDdhMDk1MGE1NjE1Y2QiLCJpYXQiOjE2NTc4OTIwNTksIm5iZiI6MTY1Nzg5MjA1OSwiZXhwIjoxNjU4NTAyMzA0LCJzdWIiOiI0Iiwic2NvcGVzIjpbXX0.vEqb4NXs075hLJeur-IqEp838tysOU1CpZ93qxtVtpo6GsHdQCsRJkmZbNb1scWja7FfAf3aiSOOmSQVwDlzVX_tU2TdZ6OD85hKG_w_ZjWCRnuBB5TiJdXCZmcRlJQ-BuJTnBAl5ScqIAcBifS9chRuTM-AqviOU9HyBkzbgJsZSb9wIigUMx9TIu1rjAg6wXvRBnNRtu6iwsy-fcLcW8PcaHk4X6Y_Y9whiG9eBKywd9AkrGxnYmgzUpyeR6R92zIf4ao07qH9Om2KtJimubyoneJIASR281X-kpmrgTb0wGvpmOFT9_0ZpFUFCQIAcEIdepWVjjIvoiff4X3CXeMHhSFkvxCctenaNFiz859c-Vc-mUo3Po-G9x3KY1a8-0tfhpOv6tOFzD0Hl_dyKWcCEUJjiGgSgTCZWxC1_g5kjVE175jP-3ECzrQkOFjxQsM7BPY1vejUgr7I5qA2PVjGgo6ekt5632dVxCEaz4XjvUrhcR09k52LLTGlGGi-AuMbh4QT-ZNIDBeZJ3Ptd1YdWbKAwuixBYZW9iTv-JJC-rawapbMGC1xO7WC7AEUprjUZvTP4nVgnfa3QOA5RLCnmf15tgXk2HLVOaKEFgfdpoX8z-Vg_GzinnYssPKq2PU5XVmoT7oL1Yr55HQFJp3ZuZ4RsLfXGfIM_--JuG0';
                $response = json_decode($client->request('POST', 'https://sunapp1.ddev.site/oauth/token', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token ],
                    'form_params' => [
                        'grant_type' => 'password',
                        'client_id' => '2',
                        'client_secret' => 'UJyiUsDkhMHRgGlMGADVSkIJAOE44Lr0OAKl6dJU',
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
