<?php

namespace SunAppModules\Core\Entities;

use Nwidart\Modules\Json;

class Modules extends Model
{
    protected $namespace = 'core::modules';
    protected $actions = [
        'enable'
    ];
    protected $fillable = [
        'name',
        'path',
        'alias',
        'description',
        'keywords',
        'active',
        'requires',
        'version',
        'versions',
        'composer',
    ];

    protected $appends = [
        'moduleStatus',
        'requiresArray',
        'checkWhereUsedModule'
    ];


    public function getMetaParams()
    {
        $all = self::count();

        return [
            "counter" => [
                'all' => $all,
            ],
            "moduleStatuses" => $this::getModuleStatuses()
        ];
    }

    public static function getModuleStatuses()
    {
        if (!file_exists($path = storage_path('app/modules_statuses.json'))) {
            $this->error("File 'modules_statuses.json' does not exist in your project root.");
            return;
        }

        $modules = Json::make($path);
        $modules = $modules->all();
        return $modules;
    }

    public function getRequiresArrayAttribute()
    {
        return json_decode($this->requires, true);
    }

    public function getModuleStatusAttribute()
    {
        $status = $this->getModuleStatuses();

        if (isset($status[$this->name])) {
            return $status[$this->name];
        }
    }

    public function getCheckWhereUsedModuleAttribute()
    {
        $arr = [];
        $modules = Modules::select('name', 'requires')->get();
        foreach ($modules as $module) {
            $json = json_decode($module->requires, true);
            foreach ($json as $j) {
                if ($j == $this->alias) {
                    array_push($arr, $module->name);
                }
            }
        }
        return $arr;
    }
}
