<?php

namespace Tussendoor\Http;

class Client
{
    protected $baseUri;

    /**
     * Pass an optional base URL to the class. This is used in subsequence requests.
     * @param string $baseUri
     */
    public function __construct($baseUri = null)
    {
        if (!empty($baseUri)) {
            $this->baseUri = $baseUri instanceof Url ? $baseUri : new Url($baseUri);
        }
    }

    /**
     * Perform a request. Pass in the type (either GET, POST, HEAD, or PUT), an URL and optional and
     * additional headers. The URL can be relative if a base URI was given.
     * @param  string  $type    GET, POST, HEAD, or PUT
     * @param  string  $url     Relative if a base URI was given when constructing
     * @param  array   $headers
     * @return Request
     */
    public function request($type, $url, $headers = null)
    {
        return new Request($type, $this->resolveUrl($url), $headers);
    }

    /**
     * Perform a normal GET request. The URL can be relative if a base URI was supplied.
     * @param  string  $url
     * @return Request
     */
    public function get($url)
    {
        return $this->request('GET', $url);
    }

    /**
     * Perform a normal POST request. The URL can be relative if a base URI was supplied. The payload
     * is optional and can be set after this method as a Request instance is returned.
     * @param  string  $url
     * @param  array   $payload
     * @return Request
     */
    public function post($url, $payload = null)
    {
        $request = $this->request('POST', $url);
        
        if (!empty($payload)) {
            $request->setHeader('body', is_array($payload) ? http_build_query($payload) : $payload);
        }

        return $request;
    }

    /**
     * Figure out if we need to append a relative URL to the base URI or if we should
     * treat it like an absolute URL.
     * @param  string $url
     * @return string
     */
    protected function resolveUrl($url)
    {
        if ($this->hasBaseUri()) {
            return $this->resolveRelativeUrl($url);
        }

        return $this->resolveAbsoluteUrl($url);
    }

    /**
     * Wether or not a base URI was supplied when constructing.
     * @return bool
     */
    protected function hasBaseUri()
    {
        return $this->baseUri !== null;
    }

    /**
     * Append a relative url. Currently only supports the path.
     * @param  string $url
     * @return Url
     */
    protected function resolveRelativeUrl($url)
    {
        $baseUrl = clone $this->baseUri;

        return $baseUrl->setPath($url);
    }

    /**
     * Create a new Url instance from the given URL. Throws a InvalidArgumentException if the
     * given URL is not valid.
     * @param  string $url
     * @return Url
     */
    protected function resolveAbsoluteUrl($url)
    {
        return new Url($url);
    }
}
