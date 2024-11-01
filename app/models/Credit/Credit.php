<?php

namespace Tussendoor\Billink\Credit;

use Tussendoor\Billink\Model;

// description, amount and invoiceNumber
class Credit extends Model
{
    public function __construct($orderNumber, $creditAmount, $description = null)
    {
        $this->hydrate(compact('orderNumber', 'creditAmount', 'description'));
    }
}
