<?php

namespace Tussendoor\Billink\Response;

use Tussendoor\Billink\Workflow\Invoice;
use Tussendoor\Billink\Helpers\Collection;

// <RESULT>MSG</RESULT>
// <MSG>
//     <CODE>500</CODE>
//     <STATUSES>
//         <ITEM>
//             <INVOICENUMBER>00001</INVOICENUMBER>
//             <MESSAGE>Success</MESSAGE>
//             <CODE>500</CODE>
//         </ITEM>
//         <ITEM>
//             <INVOICENUMBER>00002</INVOICENUMBER>
//             <MESSAGE>Order workflow is allready started</MESSAGE>
//             <CODE>707</CODE>
//         </ITEM>
//     </STATUSES>
// </MSG>
class Workflow extends Response
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
