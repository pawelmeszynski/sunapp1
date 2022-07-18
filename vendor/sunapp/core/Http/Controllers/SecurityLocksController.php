<?php

namespace SunAppModules\Core\Http\Controllers;

use Bouncer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use SunAppModules\Core\Entities\SecurityExceptions;
use SunAppModules\Core\Entities\SecurityLocks;
use SunAppModules\Core\Forms\LocksForm;
use SunAppModules\Core\Repositories\Repository;

class SecurityLocksController extends Controller
{
    protected $prefix = 'core::locks';
    protected $class = SecurityLocks::class;

    protected $formClass = LocksForm::class;

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
            'active' => '=',
            'blocked' => '=',
            'ip_address' => 'like',
        ]);
    }

    /**
     * Display a listing of the resource.
     * @return Response|View
     */
    public function index(Request $request)
    {
        if (!Bouncer::can('show', $this->class)) {
            abort(403);
        }
        if ($request->ajax()) {
            return $this->repository->paginate();
        }

        $this->prepareForm();

        return theme_view(
            $this->prefix . '.index',
            ['items' => $this->items, 'form' => $this->itemForm, 'item' => $this->item]
        );
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response|View
     */
    public function show($id, Request $request)
    {
        $item = $this->item->findOrFail($id);
        if (!Bouncer::can('show', $item)) {
            abort(403);
        }
        if ($request->ajax()) {
            return $this->repository->find($id);
        }
        $this->prepareForm();
        $this->itemForm->setupModel($item);
        $this->itemForm->disableFields();
        return theme_view(
            $this->prefix . '.show',
            [
                'form' => $this->itemForm,
                'item' => $item
            ]
        );
    }

    public static function checkIfLock($ip)
    {
        $lock = SecurityLocks::where('ip_address', $ip)
            ->where('active', 1)
            ->where('blocked', 1)
            ->orderBy('created_at', 'desc')
            ->first();
        if (!$lock) {
            $exceptions = SecurityExceptions::where('created_at', '>', Carbon::now()->subHour())
                ->orderBy('created_at', 'desc')->get();
            if (count($exceptions) >= 5) {
                return self::createLock($ip);
            }
        }
    }

    public static function checkIfUnlock($ip)
    {
        $lock = SecurityLocks::where('ip_address', $ip)
            ->where('active', 1)
            ->where('blocked', 1)
            ->orderBy('created_at', 'desc')
            ->first();
        if ($lock) {
            if (
                Carbon::createFromFormat('Y-m-d H:i:s', $lock->blocked_to)
                    ->timestamp < Carbon::now()->timestamp
            ) {
                return $lock->forceFill(['active' => 0, 'blocked' => 0])->save();
            }
        }
    }

    public static function createLock($ip)
    {
        if (!empty($ip)) {
            return SecurityLocks::create([
                'active' => 1,
                'blocked' => 1,
                'ip_address' => $ip,
                'blocked_from' => Carbon::now(),
                'blocked_to' => Carbon::now()->addHour()
            ]);
        }
    }
}
