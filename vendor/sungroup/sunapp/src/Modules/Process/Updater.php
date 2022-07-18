<?php

namespace SunApp\Modules\Process;

use Composer\EventDispatcher\EventDispatcher;
use Composer\IO\ConsoleIO;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;
use File;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Contracts\RepositoryInterface;
use Nwidart\Modules\Migrations\Migrator;
use SunApp\Modules\IO\InfoIO;
use SunApp\Modules\Traits\Process as TraitProcess;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class Updater
{
    use TraitProcess;

    /**
     * The module name.
     *
     * @var string
     */
    protected $name;

    /**
     * The version of module being installed.
     *
     * @var string
     */
    protected $version;

    /**
     * The module repository instance.
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * The console command instance.
     *
     * @var Command
     */
    protected $console;

    protected $output;

    /**
     * The destionation path.
     *
     * @var string
     */
    protected $path;

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
    /**
     * @var bool
     */
    private $tree;

    /**
     * The constructor.
     *
     * @param string $name
     * @param string $version
     * @param string $type
     * @param bool $tree
     */
    public function __construct($name, $version = null, $type = null, $tree = false)
    {
        $this->name = $name;
        $this->version = $version;
        $this->type = $type ? $type : 'composer';
        $this->tree = $tree;
    }

    /**
     * Set destination path.
     *
     * @param string $path
     *
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Set the module repository instance.
     * @param RepositoryInterface $repository
     * @return $this
     */
    public function setRepository(RepositoryInterface $repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * Set console command instance.
     *
     * @param Command $console
     *
     * @return $this
     */
    public function setConsole(Command $console)
    {
        $this->console = $console;

        return $this;
    }

    public function setOutput(ConsoleIO $output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Set process timeout.
     *
     * @param int $timeout
     *
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Run the installation process.
     *
     * @return Process
     */
    public function run()
    {
        $module = $this->repository->find($this->getModuleName());
        if (!$module) {
            $this->output->writeError('Module not found');
            return;
        }
        $isEnabled = $module->enabled();
        $module->disable();

        $output = $this->output->getOutput();
        $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        $this->io = new InfoIO(new StringInput(''), $output, new HelperSet(array()));
        $this->io->setIO($this->output);
        $this->install_type = 'composer';
        if ($this->type != 'composer') {
            $this->install_type = 'git';
            if ($this->tree) {
                $this->install_type = 'subtree';
            }
        }

        if (trim($module->composer_package) == '') {
            $this->type = 'files';
        } else {
            $this->name = $module->composer_package;
        }

        $progress1 = $this->output->createProgressBar();
        $progress2 = $this->output->createProgressBar();
        $progress3 = $this->output->createProgressBar();

        $progress1->setMessage('Update files');
        $progress1->start(4);

        if ($this->install_type == 'composer') {
            $progress2->setMessage('Initialize composer');
            $progress2->start(6);
            $composer = $this->createComposer();
            $repositoryManager = $composer->getRepositoryManager();
            $localRepository = $repositoryManager->getLocalRepository();
            $progress2->setMessage('Find package');
            $progress2->setProgress(1);
            //$package = $localRepository->findPackage($this->name, (is_null($this->version) ? '*' : $this->version));
            $package = false;
            $package_version = (is_null($this->version) ? '*' : $this->version);
            $package_name = $this->name;
            if (!$package) {
                if ($package_version == '*' || $package_version == '') {
                    if (version_compare(PluginInterface::PLUGIN_API_VERSION, '2.0.0', '<')) {
                        $package = $this->versionSelector->findBestCandidate(
                            $package_name,
                            $package_version,
                            $this->phpVersion,
                            $this->preferredStability
                        );
                    } else {
                        $package = $this->versionSelector->findBestCandidate(
                            $package_name,
                            $package_version,
                            $this->preferredStability
                        );
                    }
                }
                if (!$package) {
                    $package = $this->repositoryManager->findPackage($package_name, $package_version);
                }
                $progress2->setMessage('Check requirments');
                $progress2->setProgress(2);
                $requiers = $this->getAllRequires($package);

                $this->runProcess($requiers, $package, $progress2, $progress3);
            } else {
                // TODO: Należy dodać obsługę uaktualnienia paczek
            }
        }

        // TODO: Trzeba dodać obsługę instalacji innych typów git,

        $this->io->write('');
        $progress1->setMessage('Run migrations');
        $progress1->setProgress(1);

        $module = $this->repository->findOrFail($this->getModuleName());
        $module->setVersions('files', $module->install_version);
        $module->setVersions('requires', $module->install_version);
        $migrator = new Migrator($module);
        //$path = str_replace(base_path(), '', $migrator->getPath());
        $migrations = $migrator->getMigrations();
        if (count($migrations) > 0) {
            $progress2->setMessage('');
            $progress2->start(count($migrations));
            $c = 0;
            foreach ($migrations as $migration) {
                $progress2->setMessage($migration);
                if (!$migrator->find($migration)->first()) {
                    $migrator->requireFiles([$migration]);
                    $migrator->up($migration);
                    $migrator->log($migration);
                }
                $c++;
                $progress2->setProgress($c);
            }
            $progress2->finish();
            $progress2->clear();
        }
        $module->setVersions('migrations', $module->install_version);
        $progress1->setMessage('Run publish');
        $progress1->setProgress(2);

        $paths = [];

        $module_path = $module->getPath();
        if (File::exists($module_path . '/Themes')) {
            foreach (File::directories($module_path . '/Themes') as $dir) {
                $dir_name = pathinfo($dir, PATHINFO_FILENAME);
                $paths[$dir] = public_path('themes/' . $dir_name . '/modules/' . $module->getLowerName());
            }
        }

        $provider = $module->getServiceProvider();
        $loader = require 'vendor/autoload.php';
        $classmap = require 'vendor/composer/autoload_classmap.php';
        $loader->addClassMap($classmap);

        app()->register($provider);
        $provider_paths = ServiceProvider::pathsToPublish(
            $provider,
            'install'
        );

        $paths = array_merge($paths, $provider_paths);
        if (count($paths) > 0) {
            $progress2->setMessage('');
            $progress2->start(count($paths));
            $files = new Filesystem();
            $c = 0;
            foreach ($paths as $from => $to) {
                $from_name = explode(DIRECTORY_SEPARATOR, $from);
                $to_name = explode(DIRECTORY_SEPARATOR, $to);
                $name = end($from_name) . ' => ' . end($to_name);
                $progress2->setMessage($name);
                if ($files->isFile($from)) {
                    $this->publishFile($from, $to, $files);
                } elseif ($files->isDirectory($from)) {
                    $this->publishDirectory($from, $to, $files);
                }
                $c++;
                $progress2->setProgress($c);
            }
            $progress2->finish();
            $progress2->clear();
        }
        $module->setVersions('publishes', $module->install_version);
        $progress1->setMessage('Run config');
        $progress1->setProgress(3);

        app('events')->dispatch('modules.' . $module->slug . '.update', [$module, $this]);

        // TODO: należy dodać obsługe konfiguracji

        $progress1->finish();
        $progress1->clear();
        $module->setVersions('configs', $module->install_version);

        if ($isEnabled) {
            $module->enable();
        }
    }

    private function getAllRequires($package, $list = [])
    {
        $minilist = [];
        $version = $this->versionSelector->findRecommendedRequireVersion($package);
        $requires = $package->getRequires();
        foreach ($requires as $require) {
            if (version_compare(PluginInterface::PLUGIN_API_VERSION, '2.0.0', '<')) {
                $pp = $this->versionSelector->findBestCandidate(
                    $require->getTarget(),
                    $require->getConstraint()->getPrettyString(),
                    $this->phpVersion,
                    $this->preferredStability
                );
            } else {
                $pp = $this->versionSelector->findBestCandidate(
                    $require->getTarget(),
                    $require->getConstraint()->getPrettyString(),
                    $this->preferredStability
                );
            }
            if (!$pp) {
                $pp = $this->remoteRepos->findPackage($require->getTarget(), $require->getConstraint());
            }
            if (!$pp) {
                abort(500, $require->getTarget());
            } else {
                $constraint = $require->getConstraint();
                if (
                    !$this->localRepos->hasPackage($pp)
                    && !$this->localRepos->findPackage($require->getTarget(), $constraint)
                ) {
                    $list = $this->getAllRequires($pp, $list);
                };
            }
        }
        $list = array_merge($list, [$package]);
        return $list;
    }

    /**
     * Publish the file to the given path.
     *
     * @param string $from
     * @param string $to
     * @return void
     */
    protected function publishFile($from, $to, $files)
    {
        if (!$files->exists($to)) {
            $this->createParentDirectory(dirname($to), $files);

            $files->copy($from, $to);
        }
    }

    /**
     * Publish the directory to the given directory.
     *
     * @param string $from
     * @param string $to
     * @return void
     */
    protected function publishDirectory($from, $to, $files)
    {
        if (!$files->exists($to)) {
            $this->createParentDirectory(dirname($to), $files);

            $files->copyDirectory($from, $to);
        }
    }
}
