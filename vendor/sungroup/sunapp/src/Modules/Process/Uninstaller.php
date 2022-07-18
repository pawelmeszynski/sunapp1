<?php

namespace SunApp\Modules\Process;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Pool;
use Composer\EventDispatcher\EventDispatcher;
use Composer\Factory;
use Composer\IO\ConsoleIO;
use Composer\Json\JsonFile;
use Composer\Json\JsonManipulator;
use Composer\Package\Version\VersionSelector;
use Composer\Plugin\PluginInterface;
use Composer\Repository\CompositeRepository;
use Composer\Repository\InstalledFilesystemRepository;
use Composer\Repository\PlatformRepository;
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
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use SunApp\Modules\Traits\Process as TraitProcess;

class Uninstaller
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

        $module_path = $module->getPath();

        $progress1->setMessage('Remove configs');
        $progress1->start(4);

        app('events')->dispatch('modules.' . $module->slug . '.uninstall', [$module, $this]);

        // TODO: należy dodać obsługe konfiguracji

        $module->setVersions('configs', 0);

        $progress1->setMessage('Remove publish');
        $progress1->setProgress(1);

        $paths = [];


        if (File::exists($module_path . '/Themes')) {
            foreach (File::glob($module_path . '/Themes/*', GLOB_ONLYDIR) as $dir) {
                $dir_name = pathinfo($dir, PATHINFO_FILENAME);
                $paths[$dir] = public_path('themes/' . $dir_name . '/modules/' . $module->getLowerName());
            }
        }

        $provider = $module->getServiceProvider();
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
                $slug_name = Str::slug(end($from_name) . '_' . end($to_name));
                $name = end($from_name) . ' => ' . end($to_name);
                $progress2->setMessage($name);
                if ($files->isFile($from)) {
                    $this->unpublishFile($from, $to, $files);
                } elseif ($files->isDirectory($from)) {
                    $this->unpublishDirectory($from, $to, $files);
                    $this->removeDirectory($to, $files);
                }
                $c++;
                $progress2->setProgress($c);
            }
            $progress2->finish();
            $progress2->clear();
        }
        $module->setVersions('publishes', 0);

        $progress1->setMessage('Remove migrations');
        $progress1->setProgress(2);

        $migrator = new Migrator($module);
        //$path = str_replace(base_path(), '', $migrator->getPath());
        $migrations = $migrator->getMigrations();
        if (count($migrations) > 0) {
            $progress2->setMessage('');
            $progress2->start(count($migrations));
            $c = 0;
            foreach ($migrations as $migration) {
                $progress2->setMessage($migration);
                if ($migrator->find($migration)->first()) {
                    $migrator->requireFiles([$migration]);
                    $migrator->down($migration);
                    $data = $migrator->find($migration);
                    if ($data) {
                        $data->delete();
                    }
                }
                $c++;
                $progress2->setProgress($c);
            }
            $progress2->finish();
            $progress2->clear();
        }
        $module->setVersions('migrations', 0);

        $progress1->setMessage('Remove files');
        $progress1->setProgress(3);

        if ($this->install_type == 'composer') {
            $progress2->setMessage('Initialize composer');
            $progress2->start(4);
            $composer = $this->createComposer();
            $repositoryManager = $composer->getRepositoryManager();
            $localRepository = $repositoryManager->getLocalRepository();
            $progress2->setMessage('Find package');
            $progress2->setProgress(1);
            $package1 = $localRepository->findPackage($this->name, (is_null($this->version) ? '*' : $this->version));

            if ($package1) {
                $progress2->setMessage('Update composer.json');
                $progress2->setProgress(2);

                $this->removeFromComposerJson($package1);

                $this->composer = null;
                $this->composer = $this->factory->createComposer(
                    $this->io,
                    $this->configFile,
                    false,
                    $this->configFilePath
                );
                $installationManager = $this->composer->getInstallationManager();
                $localRepo = $this->composer->getRepositoryManager()->getLocalRepository();
                $package = $this->composer->getPackage();
                $config = $this->composer->getConfig();

                $progress2->setMessage('Generating optimized autoload files');
                $progress2->setProgress(3);

                $generator = $this->composer->getAutoloadGenerator();
                $generator->setDevMode(false);
                $generator->setClassMapAuthoritative(false);
                $generator->setApcu(false);
                $generator->setRunScripts(false);
                $numberOfClasses = $generator->dump(
                    $config,
                    $localRepo,
                    $package,
                    $installationManager,
                    'composer',
                    true
                );

                $this->eventDispatcher = new EventDispatcher($this->composer, $this->io);
                $this->eventDispatcher->dispatchScript(ScriptEvents::POST_UPDATE_CMD, false);

                $this->uninstallComposerPackage($package1);

                $progress2->finish();
                $progress2->clear();
            }
        } else {
            $files = new Filesystem();
            $files->deleteDirectory($module_path);
        }

        // TODO: Trzeba dodać obsługe odinstalowania innych typów git,

        $module->setVersions('requires', 0);
        $module->setVersions('files', 0);
        DbModule::where('alias', $module->alias)->delete();

        $module->delete();

        $this->io->write('');

        $progress1->finish();
        $progress1->clear();
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

    private function uninstallComposerPackage($package)
    {
        $this->localRepository = $this->installedRepository;
        $uninstall = new UninstallOperation($package);
        $installer = $this->composer->getInstallationManager();
        $installer->uninstall($this->localRepository, $uninstall);

        if ($this->localRepository->hasPackage($package)) {
            $this->localRepository->removePackage($package);
        }
    }

    private function removeFromComposerJson($package)
    {
        $requirements = [$package->getPrettyName() => $package->getPrettyVersion()];
        $this->file = $this->composerFile;

        $this->newlyCreated = !file_exists($this->file);
        if ($this->newlyCreated && !file_put_contents($this->file, "{\n}\n")) {
            $this->io->write('<error>' . $this->file . ' could not be created.</error>');

            return 1;
        }
        if (!is_readable($this->file)) {
            $this->io->write('<error>' . $this->file . ' is not readable.</error>');

            return 1;
        }
        if (!is_writable($this->file)) {
            $this->io->write('<error>' . $this->file . ' is not writable.</error>');

            return 1;
        }

        if (filesize($this->file) === 0) {
            file_put_contents($this->file, "{\n}\n");
        }

        $this->json = new JsonFile($this->file);
        $this->composerBackup = file_get_contents($this->json->getPath());
        $sortPackages = $this->config->get('sort-packages');
        $requireKey = 'require';
        $removeKey = 'require-dev';
        if (!$this->updateFileCleanly($this->json, $requirements, $requireKey, $removeKey, $sortPackages)) {
            $composerDefinition = $this->json->read();
            foreach ($requirements as $package => $version) {
                $composerDefinition[$requireKey][$package] = $version;
                unset($composerDefinition[$removeKey][$package]);
            }
            $this->json->write($composerDefinition);
        }

        $this->io->write(
            '<info>' . $this->file . ' has been ' . ($this->newlyCreated ? 'created' : 'updated') . '</info>'
        );


        $localRepo = $this->repositoryManager->getLocalRepository();
        $localRepo->reload();

        $this->locker = $this->composer->getLocker();
        $lock_data = $this->locker->getLockData();
        $updatedLock = $this->locker->setLockData(
            array_diff($this->localRepository->getCanonicalPackages(), $this->composer->getPackage()->getDevRequires()),
            null,
            $lock_data['platform'],
            $lock_data['platform-dev'],
            $lock_data['aliases'],
            $lock_data['minimum-stability'],
            $lock_data['stability-flags'],
            $lock_data['prefer-stable'],
            $lock_data['prefer-lowest'],
            $this->config->get('platform') ?: array()
        );
        $this->localRepository->write();
        if ($updatedLock) {
            $this->io->write('<info>Writing lock file</info>');
        }
    }

    /**
     * @param array $links
     * @return array
     */
    private function extractPlatformRequirements($links)
    {
        $platformReqs = array();
        foreach ($links as $link) {
            if (preg_match(PlatformRepository::PLATFORM_PACKAGE_REGEX, $link->getTarget())) {
                $platformReqs[$link->getTarget()] = $link->getPrettyConstraint();
            }
        }

        return $platformReqs;
    }

    private function updateFileCleanly($json, array $new, $requireKey, $removeKey, $sortPackages)
    {
        $contents = file_get_contents($json->getPath());

        $manipulator = new JsonManipulator($contents);

        foreach ($new as $package => $constraint) {
            if (!$manipulator->removeSubNode($requireKey, $package)) {
                return false;
            }
        }

        file_put_contents($json->getPath(), $manipulator->getContents());

        return true;
    }

    /**
     * Publish the file to the given path.
     *
     * @param string $from
     * @param string $to
     * @param $files
     * @return void
     */
    protected function unpublishFile($from, $to, $files)
    {
        if ($files->exists($to)) {
            $files->delete($to);
        }
    }

    /**
     * Publish the directory to the given directory.
     *
     * @param string $from
     * @param string $to
     * @param $files
     * @return void
     */
    protected function unpublishDirectory($from, $to, $files)
    {
        if ($files->exists($to)) {
            $files->deleteDirectory($to);
        }
    }

    protected function removeDirectory($to, $files)
    {
        if (
            $files->exists($to)
            && Str::contains(trim($to, DIRECTORY_SEPARATOR), trim(public_path(), DIRECTORY_SEPARATOR))
            && count(glob(rtrim($to, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "*")) === 0
        ) {
            $files->deleteDirectory($to);
        }
        $ex = explode(DIRECTORY_SEPARATOR, $to);
        array_pop($ex);
        $dir = implode(DIRECTORY_SEPARATOR, $ex);
        if (
            count($ex) > 0
            && trim($dir, DIRECTORY_SEPARATOR) != trim(public_path(), DIRECTORY_SEPARATOR)
        ) {
            $this->removeDirectory($dir, $files);
        }
    }
}
