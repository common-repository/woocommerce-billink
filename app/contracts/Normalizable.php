<?php

namespace Tussendoor\Billink\Contracts;

interface Normalizable
{
    /**
     * Return normalized data. Used for serialization.
     * @return array
     */
    public function normalize();
}
