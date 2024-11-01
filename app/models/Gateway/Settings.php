<?php

namespace Tussendoor\Billink\Gateway;

class Settings
{
    /**
     * Unaltered options, stored as an array.
     * @var array
     */
    protected $options;

    public function __construct($pluginIdentifier, $gatewayIdentifier)
    {
        $optionKey = sprintf('%s%s_settings', $pluginIdentifier, $gatewayIdentifier);
        $this->options = get_option($optionKey, []);
    }

    /**
     * Magic getter for easy access to settings belonging to the gateway. Applies
     * mutators if the given setting has one.
     * @param  string $name Name of the setting
     * @return mixed  The value of the setting
     */
    public function __get($name)
    {
        if ($this->hasOption($name) === false) {
            return null;
        }

        $value = $this->getOptionValue($name);
        if ($this->settingHasMutator($name)) {
            $value = $this->mutateSetting($name, $value);
        }

        return $value;
    }

    /**
     * Check if the given option exists in the options array.
     * @param  string $name
     * @return bool
     */
    public function hasOption($name)
    {
        return isset($this->options[$name]);
    }

    /**
     * Get a option value (unaltered) from the options array.
     * @param  string     $name
     * @param  mixed|null $default
     * @return mixed
     */
    public function getOptionValue($name, $default = null)
    {
        return $this->hasOption($name) ? $this->options[$name] : $default;
    }

    /**
     * Mutator method to return a boolean instead of a string value
     * @param  string $original
     * @return bool
     */
    protected function getShowBirthdateSetting($original)
    {
        return $original === 'yes';
    }

    /**
     * Mutator method to return a boolean instead of a string value
     * @param  string $original
     * @return bool
     */
    protected function getTestmodeSetting($original)
    {
        return $original === 'yes';
    }

    /**
     * Mutator method to return a boolean instead of a string value
     * @param  string $original
     * @return bool
     */
    protected function getDebugSetting($original)
    {
        return $original === 'yes';
    }

    /**
     * Mutator method to return a boolean instead of a string value
     * @param  string $original
     * @return bool
     */
    protected function getDutchOnlySetting($original)
    {
        return $original === 'yes';
    }

    /**
     * Check if the given setting name has a mutator in this class.
     * @param  string $name
     * @return bool
     */
    protected function settingHasMutator($name)
    {
        return method_exists($this, 'get' . $this->convertToStudly($name) . 'Setting');
    }

    /**
     * Call the mutator method, so the value can be mutated.
     * @param  string $name
     * @param  mixed  $value
     * @return mixed
     */
    protected function mutateSetting($name, $value)
    {
        return $this->{'get' . $this->convertToStudly($name) . 'Setting'}($value);
    }

    /**
     * Convert a string (setting name) to studly case.
     * @param  string $value
     * @return string
     */
    protected function convertToStudly($value)
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return str_replace(' ', '', $value);
    }
}
