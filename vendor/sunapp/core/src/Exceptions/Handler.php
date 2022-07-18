<?php

namespace SunAppModules\Core\src\Exceptions;

use http\Env\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use SunApp\Exceptions\Handler as BaseHandler;
use SunAppModules\Core\Http\Controllers\SecurityExceptionsController;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends BaseHandler
{
    /**
     * Render the given HttpException.
     *
     * @param \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderHttpException(HttpExceptionInterface $e)
    {

        if (!app()->bound('theme')) {
            return parent::renderHttpException($e);
        }

        $auth = Auth::user();

        if (
            env('SECURITY_ERROR_LOGGING', false) &&
            !config('system.front') &&
            !$auth &&
            in_array($e->getStatusCode(), [400, 401, 404, 405, 419])
        ) {
            SecurityExceptionsController::createSecurityExceptions(request(), $e->getStatusCode());
        }
        try {
            $this->registerErrorViewPaths();
            $view = app('view');
            $theme = app('theme');
            app()->singleton('theme', function ($app) use ($view, $theme) {
                // $theme = new Theme($app['config'], $app['events'], $view, $app['asset'], $app['files'],
                //     $app['breadcrumb'], $app['manifest']);
                $hints[] = base_path($theme->path());
                // This is nice feature to use inherit from another.
                if ($theme->getConfig('inherit')) {
                    // Inherit from theme name.
                    $inherit = $theme->getConfig('inherit');
                    // Inherit theme path.
                    $inheritPath = base_path($theme->path($inherit));

                    if ($theme->getFiles()->isDirectory($inheritPath)) {
                        array_push($hints, $inheritPath);
                    }
                }

                foreach ($hints as $hint) {
                    $view->addLocation($hint . '/views');
                }
                return $theme;
            });
            $loader = app('translation.loader');
            $loader->setPath(array_merge($loader->getPath(), [$theme->getThemePath() . 'lang']));
            if ($view->exists($view = "errors.{$e->getStatusCode()}")) {
                return response()->make(\Theme::view($view, [
                    'errors' => new ViewErrorBag(),
                    'exception' => $e,
                ]), $e->getStatusCode(), $e->getHeaders());
            }

            return $this->convertExceptionToResponse($e);
        } catch (\Exception $ex) {
            if (
                env('SECURITY_ERROR_LOGGING', false) &&
                !config('system.front') &&
                method_exists($ex, 'getStatusCode')
            ) {
                $view = app('view');
                if ($view->exists($view = "errors.{$ex->getStatusCode()}")) {
                    return response()->make(\Theme::view($view, [
                        'errors' => new ViewErrorBag(),
                        'exception' => $e,
                    ]), $e->getStatusCode(), $e->getHeaders());
                }
            }
            return parent::renderHttpException($e);
        }
    }
}
