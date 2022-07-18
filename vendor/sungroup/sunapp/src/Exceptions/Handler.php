<?php

namespace SunApp\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Arr;
use Psr\Log\LoggerInterface;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param \Exception $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        if ($this->shouldReport($exception)) {
            try {
                $logger = $this->container->make(LoggerInterface::class);
                if (app()->runningInConsole()) {
                    $logger->info('console');
                } else {
                    $logger->info(
                        \Request::fullUrl(),
                        [
                            'referer' => request()->headers->get('referer'),
                            'ip' => \Request::ip(),
                            'user-agent' => \Request::header('User-Agent')
                        ]
                    );
                }
            } catch (Exception $ex) {
                throw $ex;
            }
        }
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        return parent::render($request, $exception);
    }

    /**
     * Convert the given exception to an array.
     *
     * @param  \Exception  $e
     * @return array
     */
    protected function convertExceptionToArray(Exception $e)
    {
        return config('app.debug') ? [
            'status' => 'error',
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => collect($e->getTrace())->map(function ($trace) {
                return Arr::except($trace, ['args']);
            })->all(),
        ] : [
            'status' => 'error',
            'message' => $this->isHttpException($e) ? $e->getMessage() : 'Server Error',
        ];
    }
}
