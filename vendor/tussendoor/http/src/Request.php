<?php

namespace Tussendoor\Http;

use InvalidArgumentException;

// implements RequestContract
class Request
{
    protected $url;
    protected $type;
    protected $headers;
    protected $arguments;

    private $requestTypes = ['GET', 'POST', 'HEAD', 'PUT'];

    private $knownArguments = [
        'method', 'timeout', 'redirection', 'httpversion', 'user-agent', 'reject_unsafe_urls',
        'blocking', 'headers', 'cookies', 'body', 'compress', 'decompress', 'sslverify',
        'sslcertificates', 'stream', 'filename', 'limit_response_size',
    ];

    private $requestHandlers = [
        'GET'   => 'wp_safe_remote_get',
        'POST'  => 'wp_safe_remote_post',
        'HEAD'  => 'wp_safe_remote_request',
        'PUT'   => 'wp_safe_remote_request',
    ];

    /**
     * Set the request type, url and additional headers for the request.
     * @param string $type
     * @param Url    $url
     * @param array  $headers
     */
    public function __construct($type, Url $url, $headers = null)
    {
        $this->url = $url;
        $this->type = $this->parseRequestType($type);

        $this->setHeaders($headers);
    }

    /**
     * Set a request timeout after which the connection will be closed, regardless if it was successfull.
     * @param  int   $timeout
     * @return $this
     */
    public function timeout($timeout)
    {
        return $this->setArgument('timeout', (int) $timeout);
    }

    /**
     * Set the amount of redirects (hops) to follow, before closing the connection.
     * @param  int   $hops
     * @return $this
     */
    public function redirects($hops)
    {
        return $this->setArgument('redirection', (int) $hops);
    }

    /**
     * Set an user-agent.
     * @param  string $agent
     * @return $this
     */
    public function agent($agent)
    {
        return $this->setArgument('user-agent', (string) $agent);
    }

    /**
     * Toggle if the request should be treated as a blocking or non-blocking request. If set to true,
     * no response is returned.
     * @param  bool  $blocking
     * @return $this
     */
    public function blocking($blocking)
    {
        return $this->setArgument('blocking', (bool) $blocking);
    }

    /**
     * Set an individual header.
     * @param string $name
     * @param mixed  $value
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * Set an array of headers. Overwrites existing headers.
     * @param  array $headers
     * @return $this
     */
    public function headers($headers)
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }

        return $this;
    }

    /**
     * Set an individual argument for the WordPress HTTP function.
     * @param string $name
     * @param mixed  $value
     */
    public function setArgument($name, $value)
    {
        $this->arguments[$name] = $value;

        return $this;
    }

    /**
     * Set an array of arguments. Overwrites existing arguments.
     * @param  array $arguments
     * @return $this
     */
    public function arguments($arguments)
    {
        foreach ($arguments as $name => $value) {
            $this->setArgument($name, $value);
        }

        return $this;
    }

    /**
     * Execute and send the Request. Returns a Response instance.
     * @return Response
     */
    public function send()
    {
        $handler = $this->resolveHandler();

        $response = call_user_func_array($handler, [
            $this->url->generate(),
            $this->resolveArguments()
        ]);

        return new Response($response);
    }

    /**
     * Set the headers in the Request object. Seperates known arguments from headers.
     * @param array $headers
     */
    protected function setHeaders($headers)
    {
        list($headers, $arguments) = $this->separateArgumentsFromHeaders($headers);
        $this->headers = $headers;
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * Resolve the WordPress request handler, based upon the request type.
     * @return Callable
     */
    protected function resolveHandler()
    {
        return $this->requestHandlers[$this->type];
    }

    /**
     * Resolve all arguments used with the handler. Injects the headers into the arguments
     * and sets the request type method.
     * @return array
     */
    protected function resolveArguments()
    {
        $this->setHeaders($this->headers);

        $arguments = $this->arguments;
        $arguments['headers'] = $this->headers;
        $arguments['method'] = $this->type;

        return $arguments;
    }

    /**
     * Parses the request method and returns the correct case. Throws an InvalidArgumentException
     * if the type is invalid. Currently accepts get, post, put and patch.
     * @param  string $type
     * @return string
     */
    protected function parseRequestType($type)
    {
        if (in_array(strtoupper($type), $this->requestTypes)) {
            return strtoupper($type);
        }

        throw InvalidArgumentException("Unknow request type {$type} supplied");
    }

    /**
     * Separate the WordPress arguments from any headers.
     * @param  array $headers
     * @return array
     */
    protected function separateArgumentsFromHeaders($arguments)
    {
        if (empty($arguments)) {
            return [[], []];
        }

        $headers = [];
        foreach ($arguments as $name => $value) {
            if (!in_array($name, $this->knownArguments)) {
                $headers[$name] = $value;
                unset($arguments[$name]);
            }
        }

        if (isset($arguments['headers'])) {
            $headers = array_merge($headers, $arguments['headers']);
            unset($arguments['headers']);
        }

        return [$headers, $arguments];
    }
}
