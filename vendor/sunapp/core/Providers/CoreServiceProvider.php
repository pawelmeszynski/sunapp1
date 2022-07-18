<?php

namespace SunAppModules\Core\Providers;

use Str;
use Auth;
use Hash;
use Event;
use SunAppModules\Core\Console\ClearDatabaseCommand;
use SunAppModules\Core\Http\Middleware\CheckIPAccess;
use Theme;
use Config;
use Bouncer;
use Illuminate\Support\Arr;
use Illuminate\Http\Response;
use Illuminate\Foundation\Mix;
use Illuminate\Routing\Router;
use Illuminate\Support\Carbon;
use Illuminate\Cache\ArrayStore;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Blade;
use SunAppModules\Core\Entities\Role;
use SunAppModules\Core\Entities\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\AliasLoader;
use SunAppModules\Core\src\Mail\Mailer;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Auth\Access\Gate;
use SunAppModules\Core\Entities\UserGroup;
use SunAppModules\Core\Entities\LoginAsUser;
use SunAppModules\Core\src\Exceptions\Handler;
use SunAppModules\Core\src\Nestedset\NestedSet;
use Illuminate\Contracts\Debug\ExceptionHandler;
use PragmaRX\Google2FALaravel\Events\LoginFailed;
use SunAppModules\Core\src\Bouncer\BouncerFacade;
use SunAppModules\Core\Http\Middleware\CheckIPLock;
use SunAppModules\Core\src\Bouncer\CachedClipboard;
use SunAppModules\Core\Http\Middleware\Authenticate;
use SunAppModules\Core\src\Bouncer\Database\Ability;
use SunAppModules\Core\Providers\MailServiceProvider;
use SunAppModules\Core\Http\Middleware\GoogleAuthenticate;
use SunAppModules\Core\Http\Middleware\EnsureEmailIsVerified;
use SunAppModules\Core\Http\Middleware\ShareErrorsFromSession;
use SunAppModules\Core\src\MinifyHTML\HTMLMinifyServiceProvider;
use SunAppModules\Core\Http\Controllers\SecurityExceptionsController;
use SunAppModules\Core\Console\DeleteAuditsCommand;

class CoreServiceProvider extends ModuleServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(Router $router, Kernel $kernel)
    {
        $loader = AliasLoader::getInstance();
        $loader->alias('Bouncer', BouncerFacade::class);
        app()->extend(\Silber\Bouncer\Bouncer::class, function () {
            return Bouncer::make()
                ->withClipboard(new CachedClipboard(new ArrayStore()))
                ->withGate($this->app->make(Gate::class))
                ->create();
        });

        Bouncer::setGate($this->app->make(Gate::class));
        Bouncer::useAbilityModel(Ability::class);
        Bouncer::useRoleModel(Role::class);

        Validator::extend('peselchecksum', function ($attribute, $value, $parameters, $validator) {
            $sum = 0;
            $weights = array(1, 3, 7, 9, 1, 3, 7, 9, 1, 3, 1);
            $customMessage = '';
            $res = false;

            if (!is_numeric($value)) {
                $customMessage = trans("core::validator.peselchecksum.not_numeric");
            } elseif (strlen($value) != 11) {
                $customMessage = trans("core::validator.peselchecksum.to_small_chars");
            } else {
                foreach (str_split($value) as $key => $value) {
                    $sum += $value * $weights[$key];
                }

                if (substr($sum % 10, -1, 1) == 0) {
                    $res = true;
                } else {
                    $customMessage = trans("core::validator.peselchecksum.invalid_checksum");
                }
            }

            $validator->addReplacer(
                'peselchecksum',
                function ($message, $attribute, $rule, $parameters) use ($customMessage) {
                    return \str_replace(':custom_message', $customMessage, $message);
                }
            );

            return $res;
        }, ':custom_message');

        Auth::macro('loginAs', function ($to_entity = false, $to_id = false, $from_id = false, $token = false) {
            $expiries = 10;
            $expiredAt = Carbon::now()->subSeconds($expiries);
            $key = app('config')['app.key'];
            LoginAsUser::where('created_at', '<', $expiredAt)->delete();
            $user = auth()->user();
            if ($to_entity && $to_id && $user && !$from_id && !$token) {
                LoginAsUser::where('user_entity_from', get_class($user))
                    ->where('user_id_from', $user->id)
                    ->where('user_entity_to', $to_entity)
                    ->where('user_id_to', $to_id)
                    ->delete();
                $token = hash_hmac('sha256', Str::random(40), $key);
                $data = LoginAsUser::create([
                    'user_entity_from' => get_class($user),
                    'user_id_from' => $user->id,
                    'user_entity_to' => $to_entity,
                    'user_id_to' => $to_id,
                    'token' => Hash::make($token)
                ]);
                return [
                    'to_id' => $data->user_id_to,
                    'from_id' => $data->user_id_from,
                    'token' => $token,
                ];
            }
            if ($to_id && $from_id && $token) {
                $data = LoginAsUser::where('user_id_from', $from_id)
                    ->where('user_id_to', $to_id)
                    ->first();
                if ($data) {
                    if (!$data->created_at->addSeconds($expiries)->isPast() && Hash::check($token, $data->token)) {
                        return auth()->loginUsingId($data->user_id_to);
                    }
                }
            }
            abort(404);
        });

        Config::set('datatables-buttons.parameters.language', trans('datatables'));
        RedirectResponse::macro('withMessage', function ($type, $message, $status = 200, $data = false) {
            if (request()->ajax()) {
                $response_data = ['status' => $type, 'message' => $message];
                if ($data && is_array($data)) {
                    $response_data = array_merge_recursive($response_data, $data);
                }
                return response()->json($response_data, $status);
            }
            return RedirectResponse::with('flash.message', $message)->with('flash.type', $type);
        });

        Response::macro('withMessage', function ($type, $message, $status = 200, $data = false) {
            if (request()->ajax()) {
                $response_data = ['status' => $type, 'message' => $message];
                if ($data && is_array($data)) {
                    $response_data = array_merge_recursive($response_data, $data);
                }
                return response()->json($response_data, $status);
            }
            return Response::with('flash.message', $message)->with('flash.type', $type);
        });

        $router->aliasMiddleware('auth', Authenticate::class);
        $router->aliasMiddleware('2fa', GoogleAuthenticate::class);
        $router->aliasMiddleware('verified', EnsureEmailIsVerified::class);
        $router->pushMiddlewareToGroup('web', ShareErrorsFromSession::class);
        $router->pushMiddlewareToGroup('web', CheckIPLock::class);
        $router->pushMiddlewareToGroup('web', CheckIPAccess::class);
        parent::boot($router, $kernel);

        Blade::directive('theme_asset', function ($params) {
            return '<?php
            theme_asset(' . $params . ');
            ?>';
        });

        Blade::directive('serve', function ($params) {
            return '<?php
                Theme::asset()->serve(' . $params . ');
            ?>';
        });

        if (config('google2fa.enabled')) {
            Event::listen('eloquent.saved: SunAppModules\Core\Entities\User', function ($user) {
                if (is_null($user->getIs2faGoogleEnabled())) {
                    $google2fa = app('pragmarx.google2fa');
                    $user->update([
                        'google2fa_secret' => $google2fa->generateSecretKey(),
                        'is2fa_google_enabled' => true
                    ]);
                }
            });
            Event::listen(LoginFailed::class, function ($user) {
                abort(redirect(URL()->previous()));
            });
        }
        if (env('SECURITY_ERROR_LOGGING', false)) {
            Event::listen('Illuminate\Auth\Events\Failed', function () {
                SecurityExceptionsController::createSecurityExceptions(request(), '401');
            });
        }

        Event::listen('modules.core.install', function ($module, $installer) {
            if (env('APP_THEME') == null || env('APP_THEME_LAYOUT') == null) {
                Config::set(['theme.themeDefault' => 'SunApp5Html']);
                $envFile = app()->environmentFilePath();
                $str = file_get_contents($envFile);
                if (env('APP_THEME') == null) {
                    $str .= "APP_THEME=SunApp5Html\n";
                    putenv('APP_THEME=SunApp5Html');
                }
                if (env('APP_THEME_LAYOUT') == null) {
                    $str .= "APP_THEME_LAYOUT=app\n";
                    putenv('APP_THEME_LAYOUT=app');
                }
                if (env('APP_THEME_DIR') == null) {
                    $str .= 'APP_THEME_DIR=' . env('APP_PUBLIC_DIR') . '/themes';
                    putenv('APP_THEME_DIR=' . env('APP_PUBLIC_DIR') . '/themes');
                }
                $str .= "\n";
                $fp = fopen($envFile, 'w');
                fwrite($fp, $str);
                fclose($fp);
            }
            // Create core user groups
            if (UserGroup::count() == 0) {
                $group = UserGroup::firstOrCreate([
                    'name' => 'Wszyscy',
                    'core' => 1
                ]);
                $group->children()->firstOrCreate([
                    'name' => 'SunGroup',
                    'core' => 1
                ]);
            }

            if (Role::where('name', 'banned')->count() === 0) {
                Bouncer::forbid('banned')->everything();
            }

            $users = User::whereNull('google2fa_secret')->get();
            $google2fa = app('pragmarx.google2fa');
            foreach ($users as $user) {
                $user->update([
                    'google2fa_secret' => $google2fa->generateSecretKey(),
                    'is2fa_google_enabled' => true
                ]);
            }

            $this->fixTrees($module);

            $installedLogFile = storage_path('installed');

            $dateStamp = date('Y/m/d h:i:sa');

            if (! file_exists($installedLogFile)) {
                $message = trans('installer_messages.installed.success_log_message') . $dateStamp . "\n";

                file_put_contents($installedLogFile, $message);
            } else {
                $message = trans('installer_messages.updater.log.success_message') . $dateStamp;

                file_put_contents($installedLogFile, $message . PHP_EOL, FILE_APPEND | LOCK_EX);
            }
        });
        if (env('CMS_FRONT_MINIFY') && config('system.front')) {
            $this->app->register(HTMLMinifyServiceProvider::class);
        }

        $this->commands(
            [
                ClearDatabaseCommand::class,
                DeleteAuditsCommand::class,
            ]
        );

        $this->app->register(MailServiceProvider::class);

        $this->app['router']
            ->get('{any}', 'SunAppModules\Core\Http\Controllers\AssetsController@handleAsset')
            ->where('any', '.+(.min.css|.min.js)$');
    }

    public function register()
    {
        app()->extend('mailer', function ($command, $app) {
            $config = $app->make('config')->get('mail');
            $mailer = new Mailer(
                $app['view'],
                $app['swift.mailer'],
                $app['events']
            );

            if ($app->bound('queue')) {
                $mailer->setQueue($app['queue']);
            }

            foreach (['from', 'reply_to', 'to'] as $type) {
                $this->setGlobalAddress($mailer, $config, $type);
            }

            return $mailer;
        });

        Builder::macro('ddd', function () {
            dd($this->fullSql());
        });

        Builder::macro('fullSql', function () {
            $sql = str_replace(['%', '?'], ['%%', '%s'], $this->toSql());

            $handledBindings = array_map(function ($binding) {
                if (is_numeric($binding)) {
                    return $binding;
                }

                $value = str_replace(['\\', "'"], ['\\\\', "\'"], $binding);

                return "'{$value}'";
            }, $this->getConnection()->prepareBindings($this->getBindings()));

            $fullSql = vsprintf($sql, $handledBindings);

            return $fullSql;
        });

        Blueprint::macro('nestedSet', function () {
            NestedSet::columns($this);
        });

        Blueprint::macro('dropNestedSet', function () {
            NestedSet::dropColumns($this);
        });

        $this->app->singleton(
            ExceptionHandler::class,
            Handler::class
        );

        if (\File::exists(__DIR__ . '/../Resources/themes')) {
            foreach (\File::directories(__DIR__ . '/../Resources/themes') as $dir) {
                $dir_name = pathinfo($dir, PATHINFO_FILENAME);
                $this->publishes([
                    __DIR__ . '/../Resources/themes/' . $dir_name => public_path('themes/' . $dir_name),
                ], 'install');
            }
        }

        if (file_exists(public_path('themes/SunApp5Html/config.php'))) {
            Config::set(['auth.providers.users.model' => 'User']);
            if (env('APP_THEME') == null) {
                Config::set(['theme.themeDefault' => 'SunApp5Html']);
            }
            if (env('APP_THEME_LAYOUT') == null) {
                Config::set(['theme.layoutDefault' => 'app']);
            }
            if (!class_exists('App\User')) {
                class_alias(User::class, 'App\User');
            }
            if (class_exists("\Theme")) {
                $this->app->extend(Mix::class, function ($command, $app) {
                    return new \SunAppModules\Core\src\Mix\Mix();
                });
            }
            if (class_exists("\Theme")) {
                $translator = app('translator');
                foreach (Theme::all() as $theme) {
                    $translator->addNamespace(
                        $theme,
                        base_path(Theme::path($theme)) . '/lang'
                    );
                }
            }

            view()->replaceNamespace(
                'genealabs-laravel-caffeine',
                __DIR__ . '/../Resources/views/genealabs-laravel-caffeine',
                'genealabs-laravel-caffeine'
            );
            parent::register();
        }
    }

    /**
     * Set a global address on the mailer by type.
     *
     * @param  \Illuminate\Mail\Mailer  $mailer
     * @param  array  $config
     * @param  string  $type
     */
    protected function setGlobalAddress($mailer, array $config, $type)
    {
        $address = Arr::get($config, $type);

        if (is_array($address) && isset($address['address'])) {
            $mailer->{'always' . Str::studly($type)}($address['address'], $address['name']);
        }
    }
}
