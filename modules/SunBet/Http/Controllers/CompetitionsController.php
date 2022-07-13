<?php

namespace SunAppModules\SunBet\Http\Controllers;


use SunAppModules\Core\Http\Controllers\Controller;
use SunAppModules\Core\Repositories\Repository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use SunAppModules\SunBet\Entities\SunbetCompetition;
use SunAppModules\SunBet\Forms\CompetitionsForm;
use Bouncer;

class CompetitionsController extends Controller
{
    protected $prefix = 'sunbet::competitions';
    protected $class = SunbetCompetition::class;
    protected $formClass = CompetitionsForm::class;

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
    /**
     * Update the specified resource in storage.
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function edit($id, Request $request)
    {
        $this->item = $this->item->findOrFail($id);
        if (!Bouncer::can('edit', $this->item)) {
            abort(403);
        }
        if ($request->ajax()) {
            return $this->repository->find($id);
        }
        $this->prepareForm();
        $this->itemForm->setupModel($this->item);

        return theme_view(
            $this->prefix . '.edit',
            [
                'form' => $this->itemForm,
                'item' => $this->item
            ]
        );
    }
}
