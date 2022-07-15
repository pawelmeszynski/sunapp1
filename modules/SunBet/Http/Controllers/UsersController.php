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

}
