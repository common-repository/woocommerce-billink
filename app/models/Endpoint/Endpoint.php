<?php

namespace Tussendoor\Billink\Endpoint;

use ReflectionClass;
use Tussendoor\Billink\App;
use Tussendoor\Billink\Model;
use Tussendoor\Billink\AuthenticationHeader;
use Tussendoor\Billink\Contracts\Hydratable;
use Tussendoor\Billink\Contracts\Formattable;
use Tussendoor\Billink\Contracts\Normalizable;

abstract class Endpoint extends Model implements Hydratable, Formattable, Normalizable
{
    protected $header;
    protected $action;
    protected $workflowNumber;

    /**
     * Set the authentication header. Needs to be an AuthenticationHeader instance.
     * @param \Tussendoor\Billink\AuthenticationHeader $header
     */
    public function setAuthenticationHeader(AuthenticationHeader $header)
    {
        $this->header = $header;

        return $this;
    }

    /**
     * Return the unaltered AuthenticationHeader instance, if it is set.
     * @return \Tussendoor\Billink\AuthenticationHeader|null
     */
    public function getAuthenticationHeader()
    {
        return $this->header;
    }

    /**
     * If the instance has a header, cast it to array with the toArray() method.
     * @return array
     */
    public function normalizeAuthenticationHeader()
    {
        return $this->header ? $this->header->toArray() : [];
    }

    /**
     * Set the workflow number.
     * @param int $number
     */
    public function setWorkflowNumber($number)
    {
        $this->workflowNumber = $number;

        return $this;
    }

    /**
     * Return the workflowNumber property.
     * @return int|null
     */
    public function getWorkflowNumber()
    {
        return $this->workflowNumber;
    }

    /**
     * Get the action that needs to be performed (and supplied) at Billink. If no action
     * property is set, we'll try and guess it by the classname.
     * @return string
     */
    public function getAction()
    {
        if (!is_null($this->action)) {
            return $this->action;
        }

        return (new ReflectionClass($this))->getShortName();
    }

    public static function getHydratorHandler()
    {
        return function (array $data) {
            return new static($data);
        };
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function toArray()
    {
        return array_merge(
            ($this->header ? $this->header->toArray() : []),
            ['ACTION' => $this->action],
            $this->attributes
        );
    }

    /**
     * {@inheritdoc}
     * @param  string $format
     * @return string
     */
    public function serialize($format = 'xml')
    {
        return App::get('serializer')->serialize($this, $format);
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public static function getRootName()
    {
        return 'API';
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function normalize()
    {
        return $this->toArray();
    }

    /**
     * Return the URL on which the endpoint is reachable. Must be a relative URL.
     * @return string
     */
    abstract public function getUrlEndpoint();
}
