<?php

namespace Tussendoor\Billink\Response;

use Tussendoor\Billink\Payment\Invoice;
use Tussendoor\Billink\Helpers\Collection;

// <RESULT>MSG</RESULT>
// <MSG>
//     <INVOICES>
//         <ITEM>
//             <INVOICENUMBER>00001</INVOICENUMBER>
//             <MESSAGE>Success</MESSAGE>
//             <CODE>500</CODE>
//         </ITEM>
//         <ITEM>
//             <INVOICENUMBER>00002</INVOICENUMBER>
//             <MESSAGE>Order is already paid</MESSAGE>
//             <CODE>707</CODE>
//         </ITEM>
//     </INVOICES>
// </MSG>

class Payment extends Response
{
    /**
     * Return a collection if Payment\Invoice instances.
     * @return \Tussendoor\Billink\Helpers\Collection|false
     */
    public function getInvoices()
    {
        if ($this->isInvalid()) {
            return false;
        }

        $collection = new Collection();
        foreach ($this->msg['invoices'] as $invoice) {
            $collection->append(new Invoice($invoice));
        }

        return $collection;
    }
}
