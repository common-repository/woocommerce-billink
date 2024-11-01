<?php

namespace Tussendoor\Billink\Payment;

use Tussendoor\Billink\Model;

// description, amount and invoiceNumber
class Payment extends Model
{
    public function __construct($orderNumber, $amount, $description = null)
    {
        $this->hydrate(compact('orderNumber', 'amount', 'description'));
    }
}
