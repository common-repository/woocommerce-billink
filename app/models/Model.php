<?php

namespace Tussendoor\Billink;

/**
 * Just your average Model class.
 */
abstract class Model implements Contracts\Arrayable
{
    use Concerns\HasAttributes;

    /**
     * Pass an (optional) associative array to hydrate properties.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->hydrate($data);
    }

    /**
     * Hydrate a Model by passing an associatve array. The values will be stored as
     * properties in the model.
     * @param  array $data
     * @return $this
     */
    public function hydrate($data)
    {
        foreach ($data as $name => $value) {
            $this->setAttribute($name, $value);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function toArray()
    {
        return array_map(function ($value) {
            return $value instanceof Contracts\Arrayable ? $value->toArray() : $value;
        }, $this->attributes);
    }
}
