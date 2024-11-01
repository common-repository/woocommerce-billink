<?php

namespace Tussendoor\Billink\Endpoint;

use Tussendoor\Billink\Order\Order;
use Tussendoor\Billink\Customer\Customer;

class Check extends Endpoint
{
    protected $order;
    protected $customer;
    protected $action = 'Check';

    public function __construct(Customer $customer, Order $order)
    {
        $this->order = $order;
        $this->customer = $customer;
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getUrlEndpoint()
    {
        return '/v1/client/check';
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function normalize()
    {
        return [
            $this->normalizeAuthenticationHeader(),
            'ACTION'            => $this->getAction(),
            'ORDERAMOUNT'       => $this->order->total(),
            'WORKFLOWNUMBER'    => $this->workflowNumber,
            $this->customer,
        ];
    }
}
