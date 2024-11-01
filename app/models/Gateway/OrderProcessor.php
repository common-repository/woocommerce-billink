<?php

namespace Tussendoor\Billink\Gateway;

use WC_Order;
use WC_Customer;
use Tussendoor\Billink\Helpers\ParameterBag;
use Tussendoor\Billink\Exceptions\UnknownOrder;
use Tussendoor\Billink\Converters\Order as OrderConverter;
use Tussendoor\Billink\Converters\Customer as CustomerConverter;

/**
 * Process a WC_Order to Billink Order and Customer instances
 */
class OrderProcessor
{
    public function __invoke($order, ParameterBag $request)
    {
        return $this->process($order, $request);
    }

    /**
     * Process an order and the request into instances of WC_Order, Order and Customer.
     * @param  int|WC_Order                             $wcOrder
     * @param  \Tussendoor\Billink\Helpers\ParameterBag $request
     * @return array
     */
    public function process($wcOrder, ParameterBag $request)
    {
        $wcOrder = $this->resolveWooCommerceOrder($wcOrder);

        // Resolve the WC_Customer from the session and apply any changes to the model.
        $wcCustomer = apply_filters('billink_customer_data', \WC()->customer, $request);
        $wcCustomer->apply_changes();

        $order = (new OrderConverter($wcOrder))->parse();
        $customer = (new CustomerConverter($wcCustomer))->parse();

        $customer->birthdate = $request->get('billink_birthdate');
        $customer->chamberOfCommerce = $request->get('billink_chamber_of_commerce');
        $customer->vatNumber = $request->get('billink_vat_number');

        // Fallback for when the checkout does not contain a phonenumber, but we
        // did receive one in the request through our phone input field.
        if (!$customer->phonenumber && $request->has('billing_phone')) {
            $customer->phonenumber = $request->getDigits('billing_phone');
        }

        return [$wcOrder, $order, $customer];
    }

    /**
     * Resolve a WC_Order instance.
     * @param  int|WC_Order                                $wcOrder
     * @return \WC_Order
     * @throws \Tussendoor\Billink\Exceptions\UnknownOrder When the order cannot be resolved
     */
    protected function resolveWooCommerceOrder($wcOrder)
    {
        if (is_numeric($wcOrder)) {
            $wcOrder = wc_get_order($wcOrder);
        }

        if (!is_object($wcOrder) || $wcOrder instanceof WC_Order == false) {
            throw new UnknownOrder(sprintf(__(
                'Unknown order number %s',
                'woocommerce-gateway-billink'
            ), is_scalar($wcOrder) ? (string) $wcOrder : gettype($wcOrder)));
        }

        return $wcOrder;
    }
}
