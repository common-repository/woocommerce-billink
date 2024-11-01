<?php

namespace Tussendoor\Billink\Response;

use Tussendoor\Billink\Status\Invoice;
use Tussendoor\Billink\Helpers\Collection;

// <RESULT>MSG</RESULT>
// <MSG>
//     <CODE>200</CODE>
//     <INVOICES>
//         <INVOICE>
//             <INVOICENUMBER>00001</INVOICENUMBER>
//             <STATUS>0</STATUS>
//             <DESCRIPTION>CIB ( Current step name )</DESCRIPTION>
//         </INVOICE>
//         <INVOICE>
//             <INVOICENUMBER>00002</INVOICENUMBER>
//             <STATUS>1</STATUS>
//             <DESCRIPTION>No steps given</DESCRIPTION>
//             <PAIDOUT>1</PAIDOUT>
//         </INVOICE>
//         <INVOICE>
//             <INVOICENUMBER>00003</INVOICENUMBER>
//             <STATUS>-1</STATUS>
//             <DESCRIPTION>Order not found</DESCRIPTION>
//         </INVOICE>
//         <INVOICE>
//             <INVOICENUMBER>00005</INVOICENUMBER>
//             <STATUS>2</STATUS>
//             <DESCRIPTION>Wait for payment ( Current step name )</DESCRIPTION>
//         </INVOICE>
//     </INVOICES>
// </MSG>

class Status extends Response
{
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
