<?php

namespace Tussendoor\Billink\Endpoint;

use Tussendoor\Billink\Customer\Customer;
use Tussendoor\Billink\Order\Order as OrderModel;

class Order extends Endpoint
{
    protected $order;
    protected $customer;
    protected $action = 'Order';

    public function __construct(Customer $customer, OrderModel $order)
    {
        $this->order = $order;
        $this->customer = $customer;
    }

    /**
     * Use this to let Billink know the order should only be validated. Default false.
     * @param bool $orderValidation
     */
    public function setOrderValidation($orderValidation)
    {
        $this->order->validateOrder = (bool) $orderValidation;

        return $this;
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getUrlEndpoint()
    {
        return '/v1/client/order';
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function normalize()
    {
        $normalized = [
            $this->normalizeAuthenticationHeader(),
            'ACTION'            => $this->getAction(),
            'WORKFLOWNUMBER'    => $this->workflowNumber,
            $this->customer,
        ];

        // Normalize the order instance and unset the ORDERTOTAL value, as it's not
        // needed for the Order endpoint.
        $order = $this->order->normalize();
        unset($order['ORDERTOTAL']);

        // Check if the order needs to be validated or created directly
        if ($this->order->validate) {
            $normalized['VALIDATEORDER'] = 'Y';
        }

        return array_merge($normalized, $order);
    }
}
