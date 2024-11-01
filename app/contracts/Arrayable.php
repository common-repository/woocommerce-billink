<?php

namespace Tussendoor\Billink\Contracts;

interface Arrayable
{
    /**
     * Turn the current instance into an array.
     * @return array
     */
    public function toArray();
}
