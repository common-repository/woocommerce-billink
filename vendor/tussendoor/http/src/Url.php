<?php

namespace Tussendoor\Http;

use BadMethodCallException;
use InvalidArgumentException;

class Url
{
    protected $parts = [];
    protected $knownParts = ['scheme', 'host', 'port', 'user', 'pass', 'path', 'query', 'fragment'];

    public function __construct($url = null)
    {
        if (!empty($url)) {
            $this->hydrate($url);
        }
    }

    /**
     * Dynamically get parts of the URL. If $name is not set or is not a valid part, null is returned.
     * @param  string      $name
     * @return string|null
     */
    public function __get($name)
    {
        return $this->has($name) ? $this->parts[$name] : null;
    }

    /**
     * Dynamically set or get a part. To set a part, call setPart($value). If the part does not exist,
     * a BadMethodCallException is thrown. Call getPart() to return the value of a part. Returns
     * null if the part does not exist.
     * @param  string      $name
     * @param  array       $arguments
     * @return string|null
     */
    public function __call($name, $arguments)
    {
        if (substr($name, 0, 3) === 'get') {
            return $this->resolveGetter(strtolower(str_replace('get', '', $name)));
        }

        if (substr($name, 0, 3) === 'set') {
            return $this->resolveSetter(strtolower(str_replace('set', '', $name)), $arguments);
        }

        throw new BadMethodCallException(sprintf('Call to undefined method %s::%s()', __CLASS__, $name));
    }

    /**
     * Check if the URL has/contains a certain part.
     * @param  string $name
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, $this->parts);
    }

    /**
     * Check if the current parts produce a valid URL.
     * @return bool
     */
    public function isValid()
    {
        return parse_url($this->generate()) !== false;
    }

    /**
     * Construct a full URL based upon the parts in this class.
     * @return string
     */
    public function generate()
    {
        return sprintf(
            '%s%s%s%s%s%s',
            $this->has('scheme') ? $this->scheme.'://' : 'https://',
            $this->getBase(),
            $this->has('port') ? ':'.$this->port : '',
            $this->path,
            $this->has('query') ? '?'.$this->query : '',
            $this->has('fragment') ? '#'.$this->fragment : ''
        );
    }

    /**
     * Wether or not the https scheme is used.
     * @return bool
     */
    public function isSecure()
    {
        return $this->scheme === 'https';
    }

    /**
     * Get the base URL. In http://example.com/foo, example.com is the base.
     * @return string
     */
    public function getBase()
    {
        return $this->getHost();
    }

    /**
     * Check if the URL contains a login. Does not check if it contains a password too.
     * @return bool
     */
    public function containsLogin()
    {
        return $this->user !== null;
    }

    /**
     * If a get method is called, this method resolves it to the correct part.
     * @param  string      $name
     * @return string|null Null if the part does not exist or is empty.
     */
    protected function resolveGetter($name)
    {
        if ($this->isValidPart($name) && $this->has($name)) {
            return $this->$name; // Picked-up by __get()
        }

        return null;
    }

    /**
     * If a set method is called, this method sets it. Does not check the values supplied.
     * @param  string $name
     * @param  array  $arguments
     * @return $this
     */
    protected function resolveSetter($name, $arguments)
    {
        if ($this->isValidPart($name)) {
            $this->parts[$name] = reset($arguments);

            return $this;
        }

        throw new BadMethodCallException("Unknown method {$name} in {__CLASS__}");
    }

    /**
     * Hydrate this class with a full URL. Uses parse_url() to get the parts.
     * @param  string $url
     * @return bool
     */
    protected function hydrate($url)
    {
        $parts = parse_url($url);
        if ($parts === false) {
            throw new InvalidArgumentException("Invalid URL {$url} supplied.");
        }

        return $this->parts = $parts;
    }

    /**
     * Check if the given $name is a valid part in the URL scheme.
     * @param  string $name
     * @return bool
     */
    protected function isValidPart($name)
    {
        return in_array($name, $this->knownParts);
    }
}
