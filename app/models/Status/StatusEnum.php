<?php

namespace Tussendoor\Billink\Status;

use MabeEnum\Enum;

class StatusEnum extends Enum
{
    const NOT_EXISTS = -1;
    const UNPAID = 0;
    const PAID = 1;
    const OPEN = 2;

    public function translated()
    {
        switch ($this->getName()) {
            case 'NOT_EXISTS':
                return __('Order does not exist', 'woocommerce-gateway-billink');
            case 'UNPAID':
                return __('Unpaid', 'woocommerce-gateway-billink');
            case 'PAID':
                return __('Paid', 'woocommerce-gateway-billink');
            case 'OPEN':
                return __('Open', 'woocommerce-gateway-billink');
        }

        return $this->getName();
    }
}
