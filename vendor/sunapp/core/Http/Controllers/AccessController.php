<?php

namespace SunAppModules\Core\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Repository;
use SunAppModules\Core\Entities\Access;
use SunAppModules\Core\Entities\Config;
use SunAppModules\Core\Forms\AccessForm;

class AccessController extends Controller
{
    protected $prefix = 'core::access';
    protected $class = Access::class;
    protected $formClass = AccessForm::class;
    protected $excludeIps = [];

    public function __construct(Repository $repository)
    {
        $this->excludeIps = config('access.exclude_ips');
        $this->item = new $this->class();
        $this->items = $this->class::query();
        $this->repository = $repository;
        $this->repository->setModel($this->class)->setSearchable([
            'ip_address_mask' => 'like',
            'w_2fa',
        ]);
        $access = Config::firstWhere('key', 'public_access');
        $this->item->public_access = $access;
        if (!$this->item->ip_address_mask) {
            $this->item->ip_address_mask = request()->ip();
        }
    }

    public function store(Request $request)
    {
        $item = new Access();
        $form = $this->form(AccessForm::class, [
            'model' => $item
        ]);
        $this->prepareForm();
        $this->itemForm->redirectIfNotValid();
        $item = Access::create($request->all());
        return redirect()->back()->withMessage(
            'success',
            trans('core::actions.create_success'),
            200,
            $this->repository->parserResult($item->fresh())
        );
    }

    public function update(Request $request, $id)
    {
        if ($request->get('restore') == true) {
            $item = Access::withTrashed()->find($id);
            $item->restore();
            return redirect()->back()->withMessage(
                'success',
                trans('core::actions.restore_success'),
                200,
                $this->repository->parserResult($item)
            );
        }

        $ip = $request->ip();

        $in_range = array_filter($this->excludeIps, function ($item) use ($ip) {
            return ip_in_range($ip, $item);
        });

        $access_config = Config::firstOrCreate(['key' => 'public_access'], ['value' => 0]);
        if ($access_config->value == 0 && !count($in_range)) {
            $accesses = Access::pluck('ip_address_mask')->toArray();
            $in_range = array_filter($accesses, function ($item) use ($ip) {
                return ip_in_range($ip, $item);
            });
            if (count($in_range)) {
                return redirect()->back()->withMessage('error', trans('core::access.you_ip_no_update'));
            }
        }

        $item = Access::find($id);
        $this->itemForm = $this->form(AccessForm::class, [
            'model' => $item
        ]);
        if (!$request->has('moved')) {
            $this->itemForm->redirectIfNotValid();
            $item->update($request->all());
        }
        return redirect()->back()->withMessage(
            'success',
            trans('core::actions.update_success'),
            200,
            $this->repository->parserResult($item->fresh())
        );
    }

    public function destroy($id, Request $request)
    {
        if ($request->get('force', 0) == true) {
            $item = Access::withTrashed()->find($id);
            $item->forceDelete();
            return redirect()->back()->withMessage('success', trans('core::actions.force_destroy_success'));
        }

        $ip = $request->ip();

        $in_range = array_filter($this->excludeIps, function ($item) use ($ip) {
            return ip_in_range($ip, $item);
        });

        $access_config = Config::firstOrCreate(['key' => 'public_access'], ['value' => 0]);
        if ($access_config->value == 0 && !count($in_range)) {
            $accesses = Access::pluck('ip_address_mask')->toArray();
            $in_range = array_filter($accesses, function ($item) use ($ip) {
                return ip_in_range($ip, $item);
            });
            if (count($in_range)) {
                return redirect()->back()->withMessage('error', trans('core::access.you_ip_no_delete'));
            }
        }

        $item = Access::find($id);
        $item->delete();

        if ($access_config->value == 0) {
            $accesses = Access::pluck('ip_address_mask')->toArray();
            if (!count($accesses)) {
                return redirect()->back()->withMessage('info', trans('core::access.empty_masks'));
            }
        }
        return redirect()->back()->withMessage('success', trans('core::actions.destroy_success'));
    }

    public function enableDisable(Request $request)
    {
        $ip = $request->ip();

        $access_config = Config::firstOrCreate(['key' => 'public_access'], ['value' => 0]);

        if ($access_config->value == 1) {
            $accesses = Access::pluck('ip_address_mask')->toArray();
            $in_range = array_filter(array_merge($this->excludeIps, $accesses), function ($item) use ($ip) {
                return ip_in_range($ip, $item);
            });
            if (count($in_range)) {
                $access_config->update(["value" => 0]);
                if (!count($accesses)) {
                    return redirect()->back()->withMessage('info', trans('core::access.empty_masks'));
                }
                return redirect()->back();
            } else {
                return redirect()->back()->withMessage('error', trans('core::access.access_change_fail_lose_access'));
            }
        } else {
            $access_config->update(["value" => 1]);
        }
        return redirect()->back();
    }
}
