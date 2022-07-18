<?php

namespace SunApp\Modules\Traits;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Pool;
use Composer\EventDispatcher\EventDispatcher;
use Composer\Factory;
use Composer\Json\JsonFile;
use Composer\Json\JsonManipulator;
use Composer\Package\Link;
use Composer\Package\Version\VersionSelector;
use Composer\Plugin\PluginInterface;
use Composer\Repository\CompositeRepository;
use Composer\Repository\InstalledFilesystemRepository;
use Composer\Repository\PlatformRepository;
use Composer\Repository\RepositorySet;
use Composer\Script\ScriptEvents;
use Illuminate\Support\Str;

trait Process
{
    /**
     * Get process instance.
     *
     * @return \Symfony\Component\Process\Process
     */
    public function getInstallProcess()
    {
        if ($this->type) {
            if ($this->tree) {
                return $this->installViaSubtree();
            }

            return $this->installViaGit();
        }

        return $this->installViaComposer();
    }

    /**
     * Get destination path.
     *
     * @return string
     */
    public function getDestinationPath()
    {
        if ($this->path) {
            return $this->path;
        }

        return $this->repository->getModulePath($this->getModuleName());
    }

    /**
     * Get git repo url.
     *
     * @return string|null
     */
    public function getRepoUrl()
    {
        switch ($this->type) {
            case 'github':
                return "git@github.com:{$this->name}.git";

            case 'github-https':
                return "https://github.com/{$this->name}.git";

            case 'gitlab':
                return "git@gitlab.com:{$this->name}.git";
                break;

            case 'bitbucket':
                return "git@bitbucket.org:{$this->name}.git";

            default:
                // Check of type 'scheme://host/path'
                if (filter_var($this->type, FILTER_VALIDATE_URL)) {
                    return $this->type;
                }

                // Check of type 'user@host'
                if (filter_var($this->type, FILTER_VALIDATE_EMAIL)) {
                    return "{$this->type}:{$this->name}.git";
                }

                return;
                break;
        }
    }

    /**
     * Get branch name.
     *
     * @return string
     */
    public function getBranch()
    {
        return is_null($this->version) ? 'master' : $this->version;
    }

    /**
     * Get module name.
     *
     * @return string
     */
    public function getModuleName()
    {
        $parts = explode('/', $this->name);

        return Str::studly(end($parts));
    }

    /**
     * Get composer package name.
     *
     * @return string
     */
    public function getPackageName()
    {
        if (is_null($this->version)) {
            return $this->name . ':dev-master';
        }

        return $this->name . ':' . $this->version;
    }

    /**
     * Install the module via git.
     *
     * @return \Symfony\Component\Process\Process
     */
    public function installViaGit()
    {
        return Process::fromShellCommandline(sprintf(
            'cd %s && git clone %s %s && cd %s && git checkout %s',
            base_path(),
            $this->getRepoUrl(),
            $this->getDestinationPath(),
            $this->getDestinationPath(),
            $this->getBranch()
        ));
    }

    /**
     * Install the module via git subtree.
     *
     * @return Process
     */
    public function installViaSubtree()
    {
        return Process::fromShellCommandline(sprintf(
            'cd %s && git remote add %s %s && git subtree add --prefix=%s --squash %s %s',
            base_path(),
            $this->getModuleName(),
            $this->getRepoUrl(),
            $this->getDestinationPath(),
            $this->getModuleName(),
            $this->getBranch()
        ));
    }

    /**
     * Install the module via composer.
     *
     * @return Process
     */
    public function installViaComposer()
    {
        return Process::fromShellCommandline(sprintf(
            'cd %s && composer require %s --update-no-dev',
            base_path(),
            $this->getPackageName()
        ));
    }

    private function installComposerPackage($package)
    {
        $this->localRepository = $this->installedRepository;
        $install = new InstallOperation($package);
        $installer = $this->composer->getInstallationManager();
        $operation = new InstallOperation($package);
        $installer->execute($this->localRepository, [$operation]);
        //$installer->install($this->localRepository, $install);

        if (!$this->localRepository->hasPackage($package)) {
            $this->localRepository->addPackage($package);
        }
    }

    private function addToComposerJson($package)
    {
        $localRepo = $this->repositoryManager->getLocalRepository();
        $localRepo->reload();
        $platformReqs = $this->extractPlatformRequirements($package->getRequires());
        $platformDevReqs = $this->extractPlatformRequirements($package->getDevRequires());

        $this->locker = $this->composer->getLocker();
        $lock_data = $this->locker->getLockData();
        $dev_packages = [];
        foreach ($this->composer->getPackage()->getDevRequires() as $dev) {
            if (!$dev instanceof Link) {
                $dev_packages[] = $dev;
            }
        }
        $updatedLock = $this->locker->setLockData(
            array_diff($this->localRepository->getCanonicalPackages(), $this->composer->getPackage()->getDevRequires()),
            $dev_packages,
            $lock_data['platform'],
            $lock_data['platform-dev'],
            $lock_data['aliases'],
            $lock_data['minimum-stability'],
            $lock_data['stability-flags'],
            $lock_data['prefer-stable'],
            $lock_data['prefer-lowest'],
            $this->config->get('platform') ?: array()
        );
        if (version_compare(PluginInterface::PLUGIN_API_VERSION, '2.0.0', '<')) {
            $this->localRepository->write();
        } else {
            $installer = $this->composer->getInstallationManager();
            $this->localRepository->write(true, $installer);
        }
        if ($updatedLock) {
            $this->io->write('<info>Writing lock file</info>');
        }

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
            if (!$manipulator->addLink($requireKey, $package, $constraint, $sortPackages)) {
                return false;
            }
            if (!$manipulator->removeSubNode($removeKey, $package)) {
                return false;
            }
        }

        file_put_contents($json->getPath(), $manipulator->getContents());

        return true;
    }

    /**
     * Create the directory to house the published files if needed.
     *
     * @param string $directory
     * @return void
     */
    protected function createParentDirectory($directory, $files)
    {
        if (!$files->isDirectory($directory)) {
            $files->makeDirectory($directory, 0755, true);
        }
    }

    private function createComposer()
    {
        $this->configFile = base_path('composer.json');
        $this->configFilePath = base_path();
        putenv('COMPOSER_HOME=' . storage_path('app/composer'));
        putenv('COMPOSER_DISABLE_XDEBUG_WARN=1');
        putenv('COMPOSER=' . $this->configFile);
        if (!file_exists(storage_path('app/composer'))) {
            mkdir(storage_path('app/composer'));
        }
        if (!file_exists(storage_path('app/composer/composer.json'))) {
            touch(storage_path('app/composer/composer.json'));
            file_put_contents(storage_path('app/composer/composer.json'), '{"require": {}}');
        }

        $this->factory = new Factory();
        $this->composerFile = Factory::getComposerFile();

        $this->composer = $this->factory->createComposer($this->io, $this->configFile, false, $this->configFilePath);
        $this->repositoryManager = $this->composer->getRepositoryManager();
        $this->localRepository = $this->repositoryManager->getLocalRepository();
        $this->remoteRepositories = $this->repositoryManager->getRepositories();
        $this->platformOverrides = $this->composer->getConfig()->get('platform') ?: array();
        $this->platformRepository = new PlatformRepository(array(), $this->platformOverrides);
        $this->config = $this->composer->getConfig();
        $this->installedRepository = new InstalledFilesystemRepository(
            new JsonFile(
                $this->config->get('vendor-dir') . '/composer/installed.json',
                null,
                $this->io
            )
        );

        if ($this->composer->getPackage()->getPreferStable()) {
            $this->preferredStability = 'stable';
        } else {
            $this->preferredStability = $this->composer->getPackage()->getMinimumStability();
        }

        $this->remoteRepos = new CompositeRepository(array_merge(
            array($this->platformRepository),
            $this->remoteRepositories
        ));

        $this->localRepos = new CompositeRepository(array_merge(
            array($this->platformRepository),
            array($this->localRepository)
        ));

        $this->phpVersion = $this->remoteRepos->findPackage('php', '*')->getPrettyVersion();
        if (version_compare(PluginInterface::PLUGIN_API_VERSION, '2.0.0', '<')) {
            $pool = new Pool();
        } else {
            $pool = new RepositorySet($this->preferredStability);
        }
        foreach ($this->remoteRepositories as $repo) {
            $pool->addRepository($repo);
        }
        $pool->addRepository($this->localRepository);
        $pool->addRepository($this->platformRepository);
        $this->versionSelector = new VersionSelector($pool);

        $this->packageReplaces = [];
        foreach ($this->localRepository->getCanonicalPackages() as $re) {
            $rep = $re->getReplaces();
            foreach ($rep as $rr) {
                $this->packageReplaces[$rr->getTarget()] = $rr->getSource();
            }
        }

        return $this->composer;
    }

    private function runProcess($requiers, $package, $progress2, $progress3)
    {
        $c = 0;
        $progress2->setMessage('Install requirements');
        $progress2->setProgress(3);

        $progress3->start(count($requiers));
        foreach ($requiers as $pkg) {
            $progress3->setMessage(
                '<info>' . $pkg->getPrettyName() . '</info> (<comment>'
                . $pkg->getPrettyVersion() . '</comment>)'
            );
            $progress3->setProgress($c);
            $c++;
            $this->installComposerPackage($pkg);
            $progress3->setProgress($c);
        }
        $progress3->finish();
        $progress3->clear();

        $progress2->setMessage('Update composer.json');
        $progress2->setProgress(4);

        $this->addToComposerJson($package);

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
        $progress2->setProgress(5);

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
        $progress2->finish();
        $progress2->clear();
    }
}
