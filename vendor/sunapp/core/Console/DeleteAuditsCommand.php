<?php

namespace SunAppModules\Core\Console;

use Illuminate\Console\Command;
use SunAppModules\Core\Entities\Audit;

class DeleteAuditsCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'audit:delete
                            {days : The number of days not to be deleted}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete audits older than {days} from now.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $days = $this->argument('days');
        if ($days > 0) {
            $date = date('Y-m-d', strtotime('-' . $days . ' day'));
            $auditsToDelete = Audit::where('created_at', '<', $date)->get();

            if (count($auditsToDelete) > 0) {
                $this->info('You can delete ' . count($auditsToDelete) . ' record\s.');
                if ($this->confirm('Do you wish to continue?')) {
                    Audit::where('created_at', '<', $date)->delete();
                }
                $this->info('Success');
            } else {
                $this->info('No records to delete.');
            }
        } else {
            $this->error('Wrong days');
        }
    }
}
