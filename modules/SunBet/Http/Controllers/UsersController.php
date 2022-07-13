<?php

namespace SunAppModules\SunBet\Http\Controllers;

use Auth;
use Bouncer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use SunAppModules\Core\Forms\UserForm;
use SunAppModules\Core\Http\Controllers\Controller;
use SunAppModules\Core\Repositories\Repository;
use SunAppModules\SunBet\Entities\SunbetUser;

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
     * @param  Request  $request
     * @return Response
     */

}
