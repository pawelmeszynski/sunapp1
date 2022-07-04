<?php

namespace SunAppModules\SunBet\Http\Controllers;

use SunAppModules\Core\Http\Controllers\Controller;
use SunAppModules\Core\Repositories\Repository;
use SunAppModules\Core\src\FormBuilder\Form;
use SunAppModules\Core\Entities\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SunBetController extends Controller
{
    protected $prefix = 'sunbet::sunbet';
    protected $class = Model::class;
    protected $formClass = Form::class;

    /**
     * Controller constructor.
     * @param Repository $repository
     */
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
