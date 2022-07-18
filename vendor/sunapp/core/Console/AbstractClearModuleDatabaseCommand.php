<?php

namespace SunAppModules\Core\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use OwenIt\Auditing\Models\Audit;
use SunAppModules\Cms\Entities\CmsTermData;

abstract class AbstractClearModuleDatabaseCommand extends Command
{
    protected $description = 'Clean the database from unnecessary testing data';

    protected $moduleName = '';

    protected $list = [];

    protected $forceSkipList = [];

    ####################################################################################################################

    public static function getSignature()
    {
        return with(new static())->signature;
    }

    protected function clear()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            system('cls');
        } else {
            system('clear');
        }
        return $this;
    }

    protected function newLine(int $count = 1)
    {
        for ($i = 0; $i < $count; $i++) {
            $this->line('');
        }
    }

    protected function getRelationsByColumnName(string $column)
    {
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        foreach ($tables as $table) {
            if (Schema::hasColumn($table, $column)) {
                $_tables[] = $table;
            }
        }
        return collect($_tables ?? []);
    }

    ####################################################################################################################

    protected function getList()
    {
        return $this->list;
    }

    protected function confirmList(array $list = [])
    {
        foreach ($list as $list => $question) {
            $question['decision'] = $question['decision'] ? 'TAK' : 'NIE';

            $table[] = array_merge([
                $this->getModuleName(false)
            ], $question);
        }

        $this->clear()->table(
            ['Moduł', 'Pytanie', 'Odpowiedź'],
            $table ?? []
        );

        return $this->confirm('Czy na pewno?');
    }

    protected function executeList(array $list = [])
    {
        $list = array_map(function ($v) {
            return $v['decision'];
        }, $list);

        return array_keys(array_filter($list));
    }

    protected function truncateTables($tables = [])
    {
        if (is_string($tables)) {
            $tables = [$tables];
        }

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
                if (Schema::hasColumn($table, 'id')) {
                    DB::statement("ALTER TABLE " . $table . " AUTO_INCREMENT=1;");
                }
            } else {
                $this->newLine();
                $this->error('Tabela [' . $table . '] nie istnieje');
            }
        }
    }

    protected function deleteCommonRelations($model)
    {
        if ($model::hasMacro('media')) {
            $model->media()->detach();
        }
        if (method_exists($model, 'groups')) {
            $model->groups()->detach();
        }
        if (method_exists($model, 'categories')) {
            $model->categories()->detach();
        }
        if (method_exists($model, 'terms')) {
            $model->terms()->detach();
        }

        $audits = Audit::where('auditable_id', $model->id)
            ->where('auditable_type', get_class($model))
            ->get();

        $termData = CmsTermData::where('entity_id', $model->id)
            ->where('entity', get_class($model))
            ->get();

        foreach ($audits as $audit) {
            $audit->forceDelete();
        }

        foreach ($termData as $data) {
            $data->forceDelete();
        }
    }

    ####################################################################################################################

    protected function getModuleName(bool $prefix = true)
    {
        return $this->moduleName ? ($prefix ? 'Moduł: ' : '') . $this->moduleName : '---';
    }

    public function handle(): void
    {
        DB::statement("SET FOREIGN_KEY_CHECKS = 0;");
        $force = $this->option('force');

        do {
            if (!$force) {
                $this->clear();
            } else {
                $this->newLine();
            }
            $this->info($this->getModuleName());

            $list = $this->getList();
            $listCount = count($list);
            $listCounter = 1;
            foreach ($list as $key => $question) {
                unset($list[$key]);

                // skip command for --force mode
                if ($force && in_array($key, $this->forceSkipList)) {
                    continue;
                }

                $decision = $force ?:
                    $this->confirm($listCounter . '/' . $listCount . ' Czy usunąć: ' . $question . '?');

                $list[$key]['question'] = $question;
                $list[$key]['decision'] = $decision;

                $listCounter++;
            }
        } while (
            !$force && !$this->confirmList($list)
        );

        $this->executeList($list);
    }
}
