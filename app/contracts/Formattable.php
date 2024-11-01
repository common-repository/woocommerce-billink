<?php

namespace Tussendoor\Billink\Contracts;

interface Formattable
{
    /**
     * Returns the root element name when this class gets serialized.
     * @return string
     */
    public static function getRootName();

    /**
     * Serialize the current instance into the given $format.
     * @param  string $format
     * @return string
     */
    public function serialize($format);
}
