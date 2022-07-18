<?php

namespace SunApp\Modules\Process;

use Faker\Generator;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Nwidart\Modules\Contracts\RepositoryInterface;
use SunApp\Modules\IO\ConsoleIO;
use SunApp\Modules\IO\InfoIO;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class Store
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @var Command
     */
    protected $console;

    protected $output;

    /**
     * The process timeout.
     *
     * @var int
     */
    protected $timeout = 3360;

    /**
     * @var null|string
     */
    private $type;

    public function __construct($name, $type)
    {
        $this->name = $name;
        $this->type = $type ? $type : 'composer';
    }

    /**
     * @param RepositoryInterface $repository
     * @return $this
     */
    public function setRepository(RepositoryInterface $repository)
    {
        $this->repository = $repository;
        return $this;
    }

    /**
     * @param Command $console
     * @return $this
     */
    public function setConsole(Command $console)
    {
        $this->console = $console;
        return $this;
    }

    /**
     * @param $timeout
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * @return string
     */
    public function getModuleName()
    {
        $parts = explode('/', $this->name);
        return Str::studly(end($parts));
    }

    /**
     * @param ConsoleIO $output
     * @return $this
     */
    public function setOutput(ConsoleIO $output)
    {
        $this->output = $output;
        return $this;
    }

    public function run()
    {
        $module = $this->repository->find($this->getModuleName());
        if (!$module) {
            $this->output->writeError('Module not found');
            return;
        }

        $output = $this->output->getOutput();
        $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        $this->io = new InfoIO(new StringInput(''), $output, new HelperSet(array()));
        $this->io->setIO($this->output);
        $this->install_type = 'composer';

        $progress1 = $this->output->createProgressBar();

        $progress1->setMessage('Stored content');
        $progress1->start(1);

        $activeData = [
            '-' => null,
            1 => [
                'pl' => "1"
            ]
        ];

        $params = [
            1 => [
                'none' => [
                    'layout' => null,
                    'view' => null
                ],
                'pl' => [
                    'layout' => null,
                    'view' => null
                ]
            ],
        ];

        $moduleName = join(array_map('lcfirst', explode('-', $module->getAlias())));

        DB::beginTransaction();
        try {
            app('events')->dispatch('modules.' . $moduleName . '.content', [$activeData, $params]);
        } catch (\Exception $exception) {
            $this->io->error($exception->getMessage());
            DB::rollBack();
            return;
        }
        DB::commit();

        $progress1->finish();
    }

    /**
     * @param Generator $faker
     * @param int $iteration
     * @return mixed
     */
    public static function generateRandomFloat($faker, $iteration = 0)
    {
        if ($iteration >= 1) {
            if ($iteration % 2 == 0) {
                return $faker->randomFloat(2, 10, 99);
            } elseif ($iteration % 3 == 0) {
                return $faker->randomFloat(2, 100, 999);
            }
            return $faker->randomFloat(2, 1, 9);
        }
        return $faker->randomFloat(2);
    }

    /**
     * @param $fields
     * @param Blueprint $table
     * @return Blueprint
     * @throws \Exception
     */
    public static function checkFields($fields, $table = null)
    {
        $names = [];
        foreach ($fields as $field) {
            if (in_array($field->alias, $names)) {
                throw new \Exception("Field are the same name");
            }

            if (!is_null($table)) {
                $names[] = $field->alias;
                $table->text($field->alias)->nullable();
            }
        }

        return $table ?: null;
    }
}
