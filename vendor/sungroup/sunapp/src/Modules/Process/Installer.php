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
use Illuminate\Support\Str;
use Nwidart\Modules\Contracts\RepositoryInterface;
use Nwidart\Modules\Migrations\Migrator;
use SunApp\Modules\DbModule;
use SunApp\Modules\IO\InfoIO;
use SunApp\Modules\Traits\Process as TraitProcess;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use SunApp\Modules\Traits\ModuleHelper;

class Installer
{
    use TraitProcess;
    use ModuleHelper;

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
        $this->type = $type ? $type : (!\Str::contains($name, '/') ? 'file' : 'composer');
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
        $output = $this->output->getOutput();
        $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);

        $installed_modules = app('installed_modules');
        $check_module = $this->repository->find($this->getModuleName());
        if ($check_module) {
            $check_installed_module = $installed_modules->where('alias', $check_module->getAlias())->first();
            if ($check_installed_module) {
                $check_version = $check_module->get('version', 0);
                if (Str::startsWith($check_module->getPath(), base_path('vendor')) && $check_version == 0) {
                    $package_name = $check_module->getComposerAttr('name', false);
                    $check_version = $check_module->getComposerAttr('version', 0);
                    if ($package_name && $check_version == 0) {
                        $check_version = $this->getComposerModuleVersion($check_module, $package_name);
                    }
                }/* else {
                    $check_version = $check_module->getComposerAttr('version', $check_module->get('version', 0));
                }*/
                if ($check_version == $check_installed_module->version && !$this->console->option('force')) {
                    return;
                }
            }
        }

        $this->io = new InfoIO(new StringInput(''), $output, new HelperSet(array()));
        $this->io->setIO($this->output);
        $this->install_type = $this->type;
        if ($this->type == 'git') {
            if ($this->tree) {
                $this->install_type = 'subtree';
            }
        }

        $progress1 = $this->output->createProgressBar();
        $progress2 = $this->output->createProgressBar();
        $progress3 = $this->output->createProgressBar();

        $progress1->setMessage('Install files');
        $progress1->start(4);
        if ($this->install_type == 'composer') {
            $progress2->setMessage('Initialize composer');
            $progress2->start(6);
            $composer = $this->createComposer();
            $this->composer = $composer;
            $repositoryManager = $composer->getRepositoryManager();
            $localRepository = $repositoryManager->getLocalRepository();
            $progress2->setMessage('Find package');
            $progress2->setProgress(1);
            $package = $localRepository->findPackage($this->name, (is_null($this->version) ? '*' : $this->version));
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
                $progress2->setMessage('Check requirements');
                $progress2->setProgress(2);


                $requiers = $this->getAllRequires($package);
                $unique_requires = [];
                foreach ($requiers as $req) {
                    $nn = $req->getPrettyName() . '-' . $req->getPrettyVersion();
                    if (!isset($unique_requires[$nn]) && !$localRepository->findPackage($req->getName(), '*')) {
                        $unique_requires[$nn] = $req;
                    }
                }
                $requiers = $unique_requires;

                $this->runProcess($requiers, $package, $progress2, $progress3);
            }
        }

        $module = $this->repository->findOrFail($this->getModuleName());

        $dependencies = $module->get('requires', []);
        $dep_modules = [];
        $all_dep_modules = $this->repository->all();
        uasort($all_dep_modules, function ($a, $b) {
            if ($a->get('order') === $b->get('order')) {
                return 0;
            }
            return $a->get('order') > $b->get('order') ? 1 : -1;
        });
        foreach ($all_dep_modules as $dep) {
            if (in_array($dep->get('alias'), $dependencies)) {
                $dep_modules[] = $dep->get('alias');
            }
        }

        foreach ($dep_modules as $dep_module) {
            $dep_module = $this->repository->findOrFail($dep_module);
            $dep_installed_module = $installed_modules->where('alias', $dep_module->getAlias())->first();
            if (!$dep_installed_module || $dep_installed_module->version == null) {
                $this->output->title('Install <info>' . $dep_module->get('name') . '</info> module.');
                $installer = new Installer(
                    $dep_module->get('name'),
                    $dep_module->get('version'),
                    $dep_module->get('type')
                );
                $installer->setRepository($this->repository);
                $installer->setConsole($this->console);
                $installer->setOutput($this->output);
                $installer->setTimeout($this->timeout);
                $installer->setPath($this->path);
                $installer->run();
                $this->output->info("Module <info>" . $dep_module->get('name') . "</info> install successfully.");
            }
        }

        $version = $module->get('version', 0);
        if (Str::startsWith($module->getPath(), base_path('vendor')) && $version == 0) {
            $package_name = $module->getComposerAttr('name', false);
            $version = $module->getComposerAttr('version', 0);
            if ($package_name && $version == 0) {
                $version = $this->getComposerModuleVersion($module, $package_name);
            }
        }/* else {
            $version = $module->getComposerAttr('version', $module->get('version', 0));
        }*/

        $installed_module = $installed_modules->where('alias', $module->getAlias())->first();
        if (!$installed_module) {
            $installed_module = DbModule::create([
                'name' => $module->getName(),
                'alias' => $module->getAlias(),
                'path' => $module->getPath(),
                'keywords' => $module->get('keywords', []),
                'description' => $module->getDescription(),
                'version' => null,
                'versions' => [
                    'files' => $version,
                    'requires' => 0,
                    'migrations' => 0,
                    'publishes' => 0,
                    'configs' => 0,
                ],
                'requires' => $module->getRequires(),
                'composer' => (
                    file_exists($module->getPath() . '/composer.json')
                    && Str::startsWith($module->getPath(), base_path('vendor'))
                    ? $module->getComposerAttr('name', null)
                    : null
                )
            ]);
            $installed_modules->push($installed_module);
        }

        $loader = require base_path('vendor/autoload.php');
        $classmap = require base_path('vendor/composer/autoload_classmap.php');
        $loader->addClassMap($classmap);

        // TODO: Trzeba dodać obsługe instalacji innych typów git,

        $this->io->write('');
        $progress1->setMessage('Run migrations');
        $progress1->setProgress(1);

        $installed_module->setVersions('files', $version);
        $installed_module->setVersions('requires', $version);
        $migrator = new Migrator($module, app());
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
        $installed_module->setVersions('migrations', $version);
        $progress1->setMessage('Run publish');
        $progress1->setProgress(2);

        $paths = [];

        $module_path = $module->getPath();
        if (File::exists($module_path . '/Themes')) {
            foreach (File::directories($module_path . '/Themes') as $dir) {
                $dir_name = pathinfo($dir, PATHINFO_FILENAME);
                $paths[$dir] = public_path('themes/' . $dir_name . '/modules/' . $module->getAlias());
            }
        }

        $dep_modules = $this->getAllDeptModules($module->get('alias'));
        foreach ($dep_modules as $dep_module) {
            $dep_module = $this->repository->findOrFail($dep_module);
            $dep_module->register();
        }
        $providers = $module->get('providers', []);
        $module->register();
        foreach ($providers as $provider) {
            //app()->register($provider);
            $provider_paths = ServiceProvider::pathsToPublish(
                $provider,
                'install'
            );

            $paths = array_merge($provider_paths, $paths);
        }

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

        $installed_module->setVersions('publishes', $version);
        $progress1->setMessage('Run config');
        $progress1->setProgress(3);

        app('events')->dispatch('modules.' . $module->get('alias') . '.install', [$module, $this]);

        // TODO: należy dodać obsługe konfiguracji

        $progress1->finish();
        $progress1->clear();
        $installed_module->setVersions('configs', $version);
        $installed_module->version = $version;
        $installed_module->save();
    }

    private function getAllRequires($package, $list = [])
    {
        $minilist = [];
        $version = $this->versionSelector->findRecommendedRequireVersion($package);
        $requires = $package->getRequires();

        foreach ($requires as $require) {
            if ($this->localRepos->findPackage($require->getTarget(), $require->getConstraint())) {
                continue;
            }
            if (
                isset($this->packageReplaces[$require->getTarget()])
                && $this->localRepos->findPackage(
                    $this->packageReplaces[$require->getTarget()],
                    $require->getConstraint()
                )
            ) {
                continue;
            }

            $pp = $this->composer->getRepositoryManager()->findPackage(
                $require->getTarget(),
                $require->getConstraint()
            );

            if (!$pp) {
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
            }

            if (!$pp) {
                $pp = $this->remoteRepos->findPackage($require->getTarget(), $require->getConstraint());
            }

            if (!$pp) {
                if (version_compare(PluginInterface::PLUGIN_API_VERSION, '2.0.0', '<')) {
                    $pp = $this->versionSelector->findBestCandidate(
                        $require->getTarget(),
                        $require->getConstraint()->getPrettyString(),
                        $this->phpVersion,
                        $this->composer->getPackage()->getMinimumStability()
                    );
                } else {
                    $pp = $this->versionSelector->findBestCandidate(
                        $require->getTarget(),
                        $require->getConstraint()->getPrettyString(),
                        $this->composer->getPackage()->getMinimumStability()
                    );
                }
            }

            if (!$pp) {
                $pp = $this->remoteRepos->findPackage($require->getTarget(), '*');
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
        }
        $files->copy($from, $to);
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
        }
        $files->copyDirectory($from, $to);
    }

    protected function getAllDeptModules($module_name, $dep_modules = [])
    {
        $module = $this->repository->findByAlias($module_name);
        $dependencies = $module->get('requires', []);
        $all_dep_modules = $this->repository->all();
        uasort($all_dep_modules, function ($a, $b) {
            if ($a->get('order') === $b->get('order')) {
                return 0;
            }
            return $a->get('order') > $b->get('order') ? 1 : -1;
        });
        foreach ($all_dep_modules as $dep) {
            if (in_array($dep->get('alias'), $dependencies)) {
                $dep_modules = $this->getAllDeptModules($dep->get('alias'), $dep_modules);
                $dep_modules[] = $dep->get('alias');
            }
        }
        return $dep_modules;
    }
}
