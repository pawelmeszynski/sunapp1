<?php

namespace SunAppModules\Core\src\Exceptions;

use Exception;

class RedirectException extends Exception
{
    public const PERMANENT = 301;
    public const FOUND = 302;
    public const SEE_OTHER = 303;
    public const PROXY = 305;
    public const TEMPORARY = 307;

    private static $messages = array(
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
    );

    protected $url;

    public function __construct($url, $code = 301, $message = null, $force = false)
    {
        parent::__construct($message
            ? (string)$message
            : static::$messages[$code], (int)$code);
        if (strpos($url, '/') === 0) {
            $this->url = static::getBaseURL() . $this->url;
        }
        $this->url = (string)$url;

        if (!config('app.debug') || $force) {
            header('Location: ' . $this->url, true, $this->getCode());
            exit;
        }
    }
}
