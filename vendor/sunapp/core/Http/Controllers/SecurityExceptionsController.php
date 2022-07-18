<?php

namespace SunAppModules\Core\Http\Controllers;

use Bouncer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use SunAppModules\Core\Entities\SecurityExceptions;
use SunAppModules\Core\Entities\SecurityLocks;
use SunAppModules\Core\Forms\SecurityExceptionsForm;
use SunAppModules\Core\Repositories\Repository;

class SecurityExceptionsController extends Controller
{
    protected $prefix = 'core::sec-exceptions';
    protected $class = SecurityExceptions::class;
    protected $formClass = SecurityExceptionsForm::class;

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
            'status_code' => 'like',
            'ip_address' => 'like',
        ]);
    }

    public static function cleanTableSecurityExceptions($days)
    {
        $date = Carbon::now()->subDays($days);
        SecurityExceptions::where('created_at', '<=', $date)->each(function ($item) {
            $item->delete();
        });
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

    public static function createSecurityExceptions(Request $request, $statusCode)
    {
        self::cleanTableSecurityExceptions(30);
        $locks = new SecurityLocks();
        $ip = $request->ip();
        if (!$locks->isIPLocked($ip)) {
            $exceptionType = [
                400 => 'HTTP_BAD_REQUEST',
                401 => 'HTTP_UNAUTHORIZED',
                404 => 'HTTP_NOT_FOUND',
                405 => 'HTTP_METHOD_NOT_ALLOWED',
                419 => 'VerifyCsrfToken'
            ];
            $request->request->remove('password');
            SecurityExceptions::create([
                'status_code' => $statusCode,
                'exception_type' => $exceptionType[$statusCode],
                'ip_address' => $ip,
                'url' => $request->url(),
                'message' => $request->all(),
                'method' => $request->getMethod(),
                'user_agent' => $request->header('User-Agent')
            ]);
        } else {
            abort(403, trans('core::messages.IP_is_locked'));
        }
    }
}
