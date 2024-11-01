<?php

namespace Tussendoor\Billink\Concerns;

use DateTime;
use DomainException;

trait HasAttributes
{
    protected $attributes = [];
    protected $dirtyAttributes = [];

    protected $dateAttributes = [];

    protected $validAttributes = [];

    /**
     * Dynamically retrieve attributes on the model.
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Dynamically check if the given property exists on the model.
     * @param  string $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->propertyExists($name);
    }

    /**
     * Dynamically unset the given property from the attributes array.
     * @param string $name
     */
    public function __unset($name)
    {
        return $this->unsetProperty($name);
    }

    /**
     * Check if the given entry exists in the attributes array.
     * @param  string $name
     * @return bool
     */
    public function propertyExists($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * Unset/remove an entry from the attributes array.
     * @param string $name
     */
    public function unsetProperty($name)
    {
        unset($this->attributes[$name]);
    }

    /**
     * Get an attribute from the model.
     * @param  string $key
     * @return mixed
     */
    public function getAttribute($key, $default = null)
    {
        if (! $key) {
            return;
        }

        if (array_key_exists($key, $this->attributes) || $this->hasGetMutator($key)) {
            return $this->getAttributeValue($key);
        }

        return $default;
    }

    /**
     * Determine if a get mutator exists for an attribute.
     * @param  string $key
     * @return bool
     */
    public function hasGetMutator($key)
    {
        return method_exists($this, 'get' . $this->convertToStudly($key) . 'Attribute');
    }

    /**
     * Determine if a set mutator exists for an attribute.
     * @param  string $key
     * @return bool
     */
    public function hasSetMutator($key)
    {
        return method_exists($this, 'set' . $this->convertToStudly($key) . 'Attribute');
    }

    /**
     * Get a plain attribute (not a relationship).
     * @param  string $key
     * @return mixed
     */
    public function getAttributeValue($key, $default = null)
    {
        $value = $this->getAttributeFromArray($key, $default);

        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $value);
        }

        if ($this->isDateAttribute($key) && ! is_null($value)) {
            return $this->asDateTime($value);
        }

        return $value;
    }

    /**
     * Set a given attribute on the model.
     * @param  string $key
     * @param  mixed  $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        if ($this->hasSetMutator($key)) {
            $value = $this->setMutatedAttributeValue($key, $value);
        } elseif ($value && $this->isDateAttribute($key)) {
            $value = $this->asDateTime($value);
        }

        return $this->setAttributeValue($key, $value);
    }

    /**
     * Wether or not the model contains dirty/updated attributes.
     * @return bool
     */
    public function isDirty()
    {
        return !empty($this->dirtyAttributes);
    }

    /**
     * Get the attributes that are considered dirty.
     * @return array
     */
    public function getDirty()
    {
        $attributes = [];
        foreach ($this->dirtyAttributes as $name) {
            $attributes[$name] = $this->$name;
        }

        return $attributes;
    }

    /**
     * Set an attribute value directly on the attributes array.
     * @param string $key
     * @param mixed  $value
     */
    protected function setAttributeValue($key, $value)
    {
        if (!empty($this->validAttributes) && !in_array($key, $this->validAttributes)) {
            throw new DomainException("Unknown attribute {$key}");
        }
        $this->attributes[$key] = $value;

        $this->dirtyAttributes[] = $key;

        return $this;
    }

    /**
     * Determine if the given key contains a date value.
     * @param  string $key
     * @return bool
     */
    protected function isDateAttribute($key)
    {
        return in_array($key, $this->dateAttributes);
    }

    /**
     * Cast the given value to a DateTime instance.
     * @param  mixed    $value
     * @return DateTime
     */
    protected function asDateTime($value)
    {
        if ($value instanceof DateTime) {
            return $value;
        }

        if (is_numeric($value)) {
            return (new DateTime())->setTimestamp((int) $value);
        }

        return new DateTime($value); // And hope this'll work.
    }

    /**
     * Get an attribute from the $attributes array.
     * @param  string $key
     * @return mixed
     */
    protected function getAttributeFromArray($key, $default = null)
    {
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }

        return $default;
    }

    /**
     * Get the value of an attribute using its mutator.
     * @param  string $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function mutateAttribute($key, $value)
    {
        return $this->{'get' . $this->convertToStudly($key) . 'Attribute'}($value);
    }

    /**
     * Set the value of an attribute using its mutator.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function setMutatedAttributeValue($key, $value)
    {
        return $this->{'set' . $this->convertToStudly($key) . 'Attribute'}($value);
    }

    /**
     * Convert the given value to Studly Case.
     * @param  string $value
     * @return string
     */
    protected function convertToStudly($value)
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return str_replace(' ', '', $value);
    }
}
