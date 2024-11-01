<?php

namespace Tussendoor\Billink;

final class AuthenticationHeader implements Contracts\Arrayable
{
    protected $version = 'BILLINK2.0';
    protected $username;
    protected $authPass;

    /**
     * Pass the username and password to setup the auth header. The version is optional.
     * @param string $username
     * @param string $authPass
     * @param string $version
     */
    public function __construct($username, $authPass, $version = '')
    {
        $this->username = $username;
        $this->authPass = $authPass;
        if (!empty($version)) {
            $this->version = $version;
        }
    }

    /**
     * Overwrite the Billink API version.
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Convert the class to an associate array. Usable for serialization.
     * @return array
     */
    public function toArray()
    {
        return [
            'VERSION'           => $this->version,
            'CLIENTUSERNAME'    => $this->username,
            'CLIENTID'          => $this->authPass,
        ];
    }
}
