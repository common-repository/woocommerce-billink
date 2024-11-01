<?php

namespace Tussendoor\Http;

use Exception;

class RequestException extends Exception
{
    protected $errors = [];

    public function setErrors($errors)
    {
        $this->errors = $errors;

        return $this;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getError()
    {
        return reset($this->errors);
    }
}
