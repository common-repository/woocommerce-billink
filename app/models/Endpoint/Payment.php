<?php

namespace Tussendoor\Billink\Endpoint;

use Tussendoor\Billink\Model;
use Tussendoor\Billink\Payment\Item;
use Tussendoor\Billink\Payment\PaymentList;

class Payment extends Endpoint
{
    protected $paymentList;
    protected $customer;
    protected $action = 'Payment';

    public function __construct(PaymentList $list)
    {
        $this->paymentList = $list;
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getUrlEndpoint()
    {
        return '/v1/client/payment';
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function normalize()
    {
        return [
            $this->normalizeAuthenticationHeader(),
            'ACTION'        => $this->getAction(),
            'INVOICES'      => $this->paymentList->map(function (Model $item) {
                return new Item($item->toArray());
            })->all()
        ];
    }
}
