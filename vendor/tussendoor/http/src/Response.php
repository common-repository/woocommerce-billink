<?php

namespace Tussendoor\Http;

use Exception;

class Response
{
    protected $response;

    /**
     * Set the response in the class. Throws an Exception if the response is an error.
     * @param array $response
     */
    public function __construct($response)
    {
        if (is_wp_error($response)) {
            $error = new RequestException($response->get_error_code());
            $error->setErrors($response->get_error_messages());
            
            throw $error;
        }

        $this->response = $response;
    }

    /**
     * Retrieve the body of the response.
     * @return string
     */
    public function getBody()
    {
        return wp_remote_retrieve_body($this->response);
    }

    /**
     * Retrieve all response headers.
     * @return array
     */
    public function getHeaders()
    {
        return wp_remote_retrieve_headers($this->response);
    }

    /**
     * Retrieve a specific header from the response.
     * @param  string $name
     * @return mixed
     */
    public function getHeader($name)
    {
        $header = wp_remote_retrieve_header($this->response, $name);

        return empty($header) ? null : $header;
    }

    /**
     * Retrieve the response status code.
     * @return int
     */
    public function getStatusCode()
    {
        return wp_remote_retrieve_response_code($this->response);
    }
}
