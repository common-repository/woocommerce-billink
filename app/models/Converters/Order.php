<?php

namespace Tussendoor\Billink\Converters;

use DateTime;
use WC_Order;
use WC_Customer;
use Tussendoor\Billink\Order\Currency;
use Tussendoor\Billink\Order\Order as OrderModel;

/**
 * Convert a WC_Order to a Billink Customer
 */
class Order
{
    protected $order;

    public function __construct(WC_Order $order)
    {
        $this->order = $order;
    }

    /**
     * Convert the internal WC_Order instance into our own Order model.
     * @return \Tussendoor\Billink\Order\Order
     */
    public function parse()
    {
        $date = $this->order->get_date_created();

        $orderModel = new OrderModel();
        $orderModel->orderNumber = $this->order->get_order_number();
        $orderModel->date = $date ? $date : new DateTime();
        $orderModel->additionalText = $this->order->get_customer_note();
        $orderModel->currency = Currency::byName($this->order->get_currency());
        $orderModel->items = $this->getOrderItems();

        // If the customer is set as VAT excempt, change the taxpercentage to 0.
        $customer = new WC_Customer($this->order->get_customer_id());
        if ($customer->is_vat_exempt()) {
            $orderModel->items->map(function ($orderLine) {
                $orderLine->vat = 0;

                return $orderLine;
            });
        }

        if ($this->order->get_meta('_ordered_through_billink', true, 'edit') == 1) {
            $orderModel->workflowNumber = (int) $this->order->get_meta('_billink_workflow', true, 'edit');
        }

        $orderModel->setWooCommerceOrder($this->order);
        /**
         * @todo Add additional but optional fields: TRACKANDTRACE
         */

        return $orderModel;
    }

    /**
     * Create a new OrderLines converter and convert the items in WC_Order into
     * a OrderLinesList with OrderLine instances.
     * @return \Tussendoor\Billink\Order\OrderLineList
     */
    protected function getOrderItems()
    {
        $orderItems = new OrderLines($this->order);
        $orderItemsList = $orderItems->parse();

        return $orderItemsList;
    }
}
