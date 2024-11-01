<?php

namespace Tussendoor\Billink;

use InvalidArgumentException;

class App
{
    protected static $registry = [];

    /**
     * Bind the key to a value and store it within $registry
     * @param string $key   The name of the the thing to bind
     * @param mixed  $value
     */
    public static function bind($key, $value)
    {
        static::$registry[$key] = $value;
    }

    /**
     * Check if a certain value is bound to the registry
     * @param  string $key
     * @return bool
     */
    public static function bound($key)
    {
        return array_key_exists($key, static::$registry);
    }

    /**
     * Get a dependency from the registry
     * @param  string $key
     * @return mixed
     */
    public static function get($key)
    {
        if (!array_key_exists($key, static::$registry)) {
            throw new InvalidArgumentException("No {$key} availabile in registry");
        }

        $value = static::$registry[$key];

        if ($value instanceof Helpers\Singleton) {
            return $value->get();
        }

        return $value;
    }

    /**
     * Get a config from the registery
     * @param  string $keys
     * @return mixed
     */
    public static function getConfig($keys)
    {
        $keys = explode('.', $keys);
        if (count($keys) === 0) {
            throw new InvalidArgumentException("Invalid config key");
        }

        $config = static::$registry;
        foreach ($keys as $key) {
            if (!array_key_exists($key, $config)) {
                throw new InvalidArgumentException("No {$key} availabile in registry");
            }

            $config = $config[$key];
        }

        if ($config instanceof Helpers\Singleton) {
            return $config->get();
        }

        return $config;
    }

    /**
     * Load a config file into the app registry.
     * @param  string $path
     * @return bool
     */
    public static function loadFromConfig($path)
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException("Unloadable configuration file {$path} provided");
        }

        $values = require $path;

        if (empty($values)) {
            return true;
        }

        foreach ($values as $name => $setting) {
            self::bind($name, $setting);
        }

        return true;
    }
}
