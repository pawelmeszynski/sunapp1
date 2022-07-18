<?php

namespace SunAppModules\Core\Traits;

use BadMethodCallException;
use Closure;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;

trait MacroableModel
{
    use Macroable;

    /**
     * Handle dynamic static method calls into the method.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     *
     * @throws BadMethodCallException
     */
    public static function __callStatic($method, $parameters)
    {
        if (static::hasMacro($method)) {
            $macro = static::$macros[$method];

            if ($macro instanceof Closure) {
                return call_user_func_array(Closure::bind($macro, null, static::class), $parameters);
            }

            return $macro(...$parameters);
        }

        return (new static())->$method(...$parameters);
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     *
     * @throws BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            $macro = static::$macros[$method];

            if ($macro instanceof Closure) {
                return call_user_func_array($macro->bindTo($this, static::class), $parameters);
            }

            return $macro(...$parameters);
        }

        if (in_array($method, ['increment', 'decrement'])) {
            return $this->$method(...$parameters);
        }
        return $this->forwardCallTo($this->newQuery(), $method, $parameters);
    }

    /**
     * Determine if a get mutator exists for an attribute.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasGetMutator($key)
    {
        return method_exists($this, 'get' . Str::studly($key) . 'Attribute')
            || static::hasMacro('get' . Str::studly($key) . 'Attribute');
    }
}
