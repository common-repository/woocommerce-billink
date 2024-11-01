<?php

namespace Tussendoor\Billink\Response;

// <RESULT>MSG</RESULT>
// <MSG>
//     <CODE>500</CODE>
//     <DESCRIPTION>Advies=1</DESCRIPTION>
// </MSG>
// <UUID>XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX</UUID>

class Check extends Response
{
    const POSITIVE_RESPONSE_CODE = 500;
    const NEGATIVE_RESPONSE_CODE = 501;

    public function getAdvice()
    {
        if ($this->isInvalid()) {
            return false;
        }

        return $this->msg['description'];
    }

    public function isPositive()
    {
        return $this->getCode() == self::POSITIVE_RESPONSE_CODE;
    }

    public function isNegative()
    {
        return $this->getCode() == self::NEGATIVE_RESPONSE_CODE;
    }

    public function getUuid()
    {
        return $this->isInvalid() ? false : $this->uuid;
    }
}
