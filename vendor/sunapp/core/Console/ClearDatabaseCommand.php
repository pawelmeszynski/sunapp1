<?php

namespace SunAppModules\Core\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Module;

class ClearDatabaseCommand extends Command
{
    protected $signature = 'db:clear
                            {--force : Remove all}';

    protected $description = 'Clean the database from unnecessary testing data';

    ####################################################################################################################

    private function isModuleEnabled(string $name): bool
    {
        return ($module = Module::find($name)) && $module->isEnabled();
    }

    private function getList()
    {
        // Get array list with all installed module's names without Core
        $modules = array_reverse(
            array_keys(Module::getOrdered())
        );

        // Get array list with all registered Artisan's commands
        $commands = array_keys(Artisan::all());

        foreach ($modules as $i => $module) {
            $className = '\\SunAppModules\\' . $module . '\\Console\\Clear' . $module . 'DatabaseCommand';
            if ($this->isModuleEnabled($module) && class_exists($className)) {
                $signature = trim(str_replace('{--force}', '', $className::getSignature()));
                if (!$signature || !in_array($signature, $commands)) {
                    continue;
                }
                $list[$module] = $signature;
            }
            unset($modules[$i]);
        }

        return $list ?? [];
    }

    ####################################################################################################################

    public function handle(): void
    {
        if ($force = $this->option('force')) {
            $skip = !$this->confirm('Na pewno?');
        }

        if (!isset($skip) || !$skip) {
            foreach ($this->getList() as $command) {
                $this->call($command, [
                    '--force' => $force
                ]);

                if (!$force) {
                    if (!$this->confirm('Kontynuować?')) {
                        break;
                    }
                }
            }
        }

        $this->line('');
        $this->info('Koniec');
    }
}
