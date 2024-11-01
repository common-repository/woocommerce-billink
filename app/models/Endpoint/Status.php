<?php

namespace Tussendoor\Billink\Endpoint;

use Tussendoor\Billink\Model;
use Tussendoor\Billink\Status\Item;
use Tussendoor\Billink\Order\OrderList;

class Status extends Endpoint
{
    protected $orderList;
    protected $customer;
    protected $action = 'Status';

    public function __construct(OrderList $list)
    {
        $this->orderList = $list;
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getUrlEndpoint()
    {
        return '/v1/client/status';
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
            'INVOICES'      => $this->orderList->map(function (Model $item) {
                return new Item($item->toArray());
            })->all()
        ];
    }
}
