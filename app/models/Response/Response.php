<?php

namespace Tussendoor\Billink\Response;

use Exception;
use Throwable;
use Tussendoor\Billink\App;
use Tussendoor\Billink\Contracts\Arrayable;
use Tussendoor\Billink\Concerns\HasAttributes;
use Tussendoor\Billink\Exceptions\InvalidResponse;

abstract class Response implements Arrayable
{
    use HasAttributes;

    protected $data;
    protected $endpoint;

    public function __construct($data, $endpoint)
    {
        $this->data = $data;
        $this->hydrate($data);
        $this->endpoint = $endpoint;
    }

    /**
     * Get the statuscode.
     * @return int
     */
    public function getCode()
    {
        return $this->isValid() ? $this->msg['code'] : $this->error['code'];
    }

    /**
     * Overwrite the default error labels. Must be an array where the key is the error code
     * and the value is the error label/description.
     * @param array $labels
     */
    public function setErrorLabels($labels)
    {
        $this->errorLabels = $labels;

        return $this;
    }

    /**
     * Wether or not the response is considered valid and does not contain an error.
     * @return bool
     */
    public function isValid()
    {
        return $this->propertyExists('error') === false;
    }

    /**
     * The reverse of the isValid() method.
     * @return bool
     */
    public function isInvalid()
    {
        return !$this->isValid();
    }

    /**
     * Return the error description (untranslated).
     * @return string
     */
    public function getError()
    {
        return $this->error['description'];
    }

    /**
     * Return a translated error description.
     * @return string
     */
    public function getErrorLabel()
    {
        if (!isset($this->errorLabels[$this->getCode()])) {
            return $this->getError();
        }

        return $this->errorLabels[$this->getCode()];
    }

    /**
     * Throw an InvalidResponse exception with the translated error description and code.
     * @param  string     $exceptionClass
     * @return bool
     * @throws \Exception
     */
    public function throwException($exceptionClass = InvalidResponse::class)
    {
        if ($this->isValid()) {
            return false;
        }

        $exceptionClass = new $exceptionClass($this->getErrorLabel(), $this->getCode());
        if ($this->isThrowable($exceptionClass)) {
            throw $exceptionClass;
        }

        return false;
    }

    /**
     * Generate an exception with the translated error description and code.
     * @param  string                     $exceptionClass
     * @return \Exception|\Throwable|bool
     */
    public function generateException($exceptionClass = InvalidResponse::class)
    {
        if ($this->isValid()) {
            return false;
        }

        $exceptionClass = new $exceptionClass($this->getErrorLabel(), $this->getCode());
        if ($this->isThrowable($exceptionClass)) {
            return $exceptionClass;
        }

        return false;
    }

    /**
     * Hydrate the Response class with the resposne data from Billink.
     * @param  array $data
     * @return $this
     */
    public function hydrate($data)
    {
        $this->attributes = $this->lowerArrayKey($data);

        $errors = App::get('errors');
        $this->errorLabels = isset($errors[$this->endpoint]) ? $errors[$this->endpoint] : [];

        return $this;
    }

    /**
     * {@inheritDoc}
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * Wether or not the given object can be thrown, e.g. it implements the
     * Throwable interface or, if this environment is PHP < 7, extends
     * the default Exception class.
     * @param  object $class
     * @return bool
     */
    protected function isThrowable($class)
    {
        return is_a($class, Throwable::class) || is_a($class, Exception::class);
    }

    /**
     * Turn the string array keys to lowercase recursively.
     * @param  array $array
     * @return array
     */
    protected function lowerArrayKey($array)
    {
        $array = array_change_key_case($array, CASE_LOWER);
        foreach ($array as $name => $value) {
            if (is_array($value)) {
                $array[$name] = $this->lowerArrayKey($value);
            }
        }

        return $array;
    }
}
