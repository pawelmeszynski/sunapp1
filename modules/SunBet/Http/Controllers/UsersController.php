<?php

namespace SunAppModules\SunBet\Http\Controllers;

use App\Models\Standings;
use Auth;
use Bouncer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use SunAppModules\Core\Forms\UserForm;
use SunAppModules\Core\Http\Controllers\Controller;
use SunAppModules\Core\Repositories\Repository;
use SunAppModules\SunBet\Entities\SunbetStanding;
use SunAppModules\SunBet\Entities\SunbetUser;
use Str;
use SunAppModules\SunBet\Providers\RouteServiceProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;


class UsersController extends Controller
{
    protected $prefix = 'sunbet::users';
    protected $class = SunbetUser::class;
    protected $formClass = UserForm::class;

    public function __construct(Repository $repository)
    {
        $this->item = new $this->class();
        $this->items = $this->class::query();
        $this->repository = $repository;

        $this->repository->setModel($this->class)->setSearchable([
            //
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|RedirectResponse
     */
    public function redirect($provider)
    {
        return Socialite::driver('sunbet')->redirect();
    }

    public function callback()
    {
        $usr = Socialite::driver('sunbet')->stateless()->user();
        $usr = SunbetUser::where('sunbet_id', $usr->id)->first();
        if (isset($usr)) {
            Auth::login($usr);
            return redirect("/dashboard");
        } else {
            $new = SunbetUser::create([
                    "name" => $usr->name,
                    "email" => $usr->email,
                    "google_id" => $usr->id,
                    "roles" => "user",
                    "password" => Hash::make($usr->password)
                ]);
            Auth::login($new);
            return redirect("/dashboard");
        }
    }

    public function login(): View
    {
        return view('auth.login');
    }
}
