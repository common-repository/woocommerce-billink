<?php

namespace Tussendoor\Billink;

use Closure;

class Action
{
    protected static $registeredActions = [];

    protected $action;
    protected $callback;
    protected $variables;

    /**
     * Create a new named Action
     * @param string $name
     */
    public function __construct($name)
    {
        $this->action = $name;
    }

    /**
     * Register a callback with the current Action.
     * @param  Closure|string $callback
     * @return $this
     */
    public function callback($callback)
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Add the current instance to the static array of all registered Actions.
     * @return $this
     */
    public function register()
    {
        self::$registeredActions[$this->action] = $this->callback;

        return $this;
    }

    /**
     * Give some variables to the Action when executing the callbac.
     * @param  mixed $variables,... Variadic arguments
     * @return $this
     */
    public function with(...$variables)
    {
        $this->variables = $variables;

        return $this;
    }

    /**
     * Execute an Action. If the Action (name) does not exist, null is returned
     * @return null|mixed
     */
    public function call()
    {
        return self::doAction($this->action, $this->variables);
    }

    /**
     * Execute an Action with the given name and (optional) additional arguments
     * @param  string     $name
     * @param  mixed      $variables,...
     * @return null|mixed
     */
    public static function doAction($name, ...$variables)
    {
        if (!array_key_exists($name, self::$registeredActions)) {
            return null;
        }

        $callback = self::$registeredActions[$name];
        if ($callback instanceof Closure) {
            return call_user_func_array($callback, $variables);
        }

        if (strpos($callback, '@') !== false) {
            return self::callController($callback, ...$variables);
        }

        return $callback(...$variables);
    }

    /**
     * Try calling a controller and method. If it succeeds, pass the variables as well.
     * @param  string $controller
     * @param  array  $variables
     * @return mixed
     */
    protected static function callController($controller, $variables)
    {
        list($class, $method) = self::getClassAndMethodFromString($controller);
        if (!class_exists($class)) {
            return false;
        }

        $class = new $class();
        if (!method_exists($class, $method)) {
            return false;
        }

        return $class->$method(...$variables);
    }

    /**
     * Split the given controller string into a class and method.
     * @param  string $controller
     * @return array
     */
    protected static function getClassAndMethodFromString($controller)
    {
        list($class, $method) = explode('@', $controller);

        return [$class, $method];
    }
}
