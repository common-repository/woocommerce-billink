<?php

namespace Tussendoor\Billink\Helpers;

use Closure;

class Singleton
{
    protected $instance;
    protected $concrete;

    /**
     * Create a new Singleton by supplying a Closure. The Closure should return the instance
     * that needs to be handled as a Singleton.
     * @param Closure $instance
     */
    public function __construct(Closure $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Return the Singleton value. If we do not have a concrete instance yet,
     * create it and put it in the concrete property.
     * @return mixed
     */
    public function get()
    {
        if (empty($this->concrete)) {
            $this->concrete = ($this->instance)();
        }

        return $this->concrete;
    }

    /**
     * Shortcut method for creating a new Singleton.
     * @param  Closure                               $instance
     * @return \Tussendoor\Billink\Helpers\Singleton
     */
    public static function create(Closure $instance)
    {
        return new static($instance);
    }
}
