<?php

namespace SunAppModules\Core\src\DataTables\Services;

use Yajra\DataTables\Services\DataTable as BaseDataTable;

class DataTable extends BaseDataTable
{
    public function __construct()
    {
        $this->prepareResponse();
    }

    /**
     * Process dataTables needed ajax output.
     *
     * @return mixed
     */
    public function prepareResponse()
    {
        $response = false;
        if ($this->request()->has('draw') && $this->request()->ajax() && $this->request()->wantsJson()) {
            $response = app()->call([$this, 'ajax']);
        }

        if ($action = $this->request()->get('action') and in_array($action, $this->actions)) {
            if ($action == 'print') {
                $response = app()->call([$this, 'printPreview']);
            } else {
                $response = app()->call([$this, $action]);
            }
        }
        if ($response) {
            $response->send();
            exit;
        }
    }
}
