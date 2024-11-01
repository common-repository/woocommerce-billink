<?php

namespace Tussendoor\Billink\Response;

// <RESULT>MSG</RESULT>
// <MSG>
//     <CODE>200</CODE>
//     <DESCRIPTION>Order successfully added</DESCRIPTION>
// </MSG>

class Order extends Response
{
    const POSITIVE_RESPONSE_CODE = 200;

    public function getDescription()
    {
        if ($this->isInvalid()) {
            return false;
        }

        return $this->msg['description'];
    }

    public function orderWasCreated()
    {
        return $this->getCode() == self::POSITIVE_RESPONSE_CODE;
    }
}
