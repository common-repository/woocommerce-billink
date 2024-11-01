<?php

namespace Tussendoor\Billink\Exceptions;

class InvalidAddress extends UserError
{
    protected $addressString;

    /**
     * Add the invalid address to this instance, so it can later be resolved.
     * @param  string $addressString
     * @return $this
     */
    public function setAddressString($addressString)
    {
        $this->addressString = $addressString;

        return $this;
    }

    /**
     * Get the address string (if any) that is invalid.
     * @return string|null
     */
    public function getAddressString()
    {
        return $this->addressString;
    }
}
