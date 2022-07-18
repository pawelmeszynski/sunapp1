<?php

namespace SunApp\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array
     */
    protected $except = [
        'password',
        'password_confirmation',
    ];

    /**
     * Transform the given value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function transform($key, $value)
    {
        $except = array_merge($this->except, config('trim_strings.except', []));
        if (in_array($key, $except, true)) {
            return $value;
        }

        return is_string($value) ? trim($value) : $value;
    }
}
