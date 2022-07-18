<?php

namespace SunApp\Providers;

use Exception;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\QueryException;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Module;
use SunApp\Entities\User;
use SunApp\Http\Controllers\Controller;
use SunApp\Http\Middleware\CheckForCoreModule;
use SunApp\Http\Middleware\RedirectIfNoSetup;
use SunApp\Modules\DbModule;
use SunApp\Modules\Laravel\LaravelFileRepository;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Process\Process;
use Nwidart\Modules\Contracts\RepositoryInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Router $router, Kernel $kernel)
    {
        $installerConfigPath = __DIR__ . '/../../config/installer.php';
        $modulesConfigPath = __DIR__ . '/../../config/modules.php';
        $this->publishes([
            $installerConfigPath => config_path('installer.php'),
            $modulesConfigPath => config_path('modules.php'),
        ], 'config');

        $assetsPath = __DIR__ . '/../../resources/dist';
        $this->publishes([
            $assetsPath => public_path('installer'),
        ], 'assets');

        $viewsPath = __DIR__ . '/../../resources/views';
        $this->publishes([
            $viewsPath => resource_path('views/vendor/sunapp'),
        ], 'views');

        $langPath = __DIR__ . '/../../resources/lang';
        $this->publishes([
            $langPath => resource_path('lang'),
        ], 'lang');

        if (!app()->runningInConsole()) {
            if (!file_exists(public_path('installer'))) {
                Artisan::call('vendor:publish', [
                    '--force' => true,
                    '--tag' => 'assets',
                    '--provider' => AppServiceProvider::class,
                ]);
            }

            if (!file_exists(storage_path('installed'))) {
                $kernel->pushMiddleware(RedirectIfNoSetup::class);
            }
        } else {
            if (!Module::has('Core')) {
                $output = new ConsoleOutput();
                $process = new Process(['composer', 'require', "sunapp/core"], base_path());
                $process->setTimeout(3600);

                $process->run(function ($type, $buffer) use ($output) {
                    $output->write($buffer);
                });
                //\Artisan::call('module:install sunapp/core');
            }
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('path.public', function () {
            return realpath(base_path() . '/' . env('APP_PUBLIC_DIR', 'public'));
        });

        if (!class_exists('App\Http\Controllers\Controller')) {
            class_alias(Controller::class, 'App\Http\Controllers\Controller');
        }
        if (!class_exists('App\User')) {
            class_alias(User::class, 'App\User');
        }

        $this->app->register(ConsoleServiceProvider::class);

        $installerConfigPath = __DIR__ . '/../../config/installer.php';
        $this->mergeConfigFrom($installerConfigPath, 'installer');

        $modulesConfigPath = __DIR__ . '/../../config/modules.php';
        $this->mergeConfigFrom($modulesConfigPath, 'modules');

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        if (!app()->runningInConsole()) {
            if (app()->make('config')->get('app.key') == null || app()->make('config')->get('app.key') == '') {
                app()->make('config')->set('app.key', 'base64:bODi8VtmENqnjklBmNJzQcTTSC8jNjBysfnjQN59btE=');
                app()->make('config')->set('app.tmp_key', app()->make('config')->get('app.key'));

                $this->envPath = base_path('.env');
                $this->envExamplePath = base_path('.env.example');

                if (!file_exists($this->envPath)) {
                    if (file_exists($this->envExamplePath)) {
                        copy($this->envExamplePath, $this->envPath);
                    } else {
                        touch($this->envPath);
                    }

                    file_put_contents($this->envPath, preg_replace(
                        $this->keyReplacementPattern(),
                        'APP_KEY=' . app()->make('config')->get('app.key'),
                        file_get_contents($this->envPath)
                    ));
                    return redirect(request()->url());
                }
            }
        }

        $this->app->singleton('installed_modules', function ($app) {
            try {
                return DbModule::all();
            } catch (Exception $e) {
            }
            return collect([]);
        });
    }

    /**
     * Get a regex pattern that will match env APP_KEY with any random key.
     *
     * @return string
     */
    protected function keyReplacementPattern()
    {
        $escaped = preg_quote('=', '/');

        return "/^APP_KEY{$escaped}/m";
    }
}
