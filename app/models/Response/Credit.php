<?php

namespace Tussendoor\Billink\Response;

use Tussendoor\Billink\Credit\Invoice;
use Tussendoor\Billink\Helpers\Collection;

// <RESULT>MSG</RESULT>
// <MSG>
//     <STATUSES>
//         <ITEM>
//             <INVOICENUMBER>00001</INVOICENUMBER>
//             <MESSAGE>Credit applied: 10.00 EURO; Order paid.</MESSAGE>
//             <CODE>200</CODE>
//         </ITEM>
//         <ITEM>
//             <INVOICENUMBER>00002</INVOICENUMBER>
//             <MESSAGE>Credit applied: 10.00 EURO; step restarted.</MESSAGE>
//             <CODE>200</CODE>
//         </ITEM>
//     </STATUSES>
// </MSG>

class Credit extends Response
{
    public function getInvoices()
    {
        if ($this->isInvalid()) {
            return false;
        }

        $collection = new Collection();
        foreach ($this->msg['statuses'] as $invoice) {
            $collection->append(new Invoice($invoice));
        }

        return $collection;
    }
}
