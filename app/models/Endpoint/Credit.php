<?php

namespace Tussendoor\Billink\Endpoint;

use Tussendoor\Billink\Model;
use Tussendoor\Billink\Credit\Item;
use Tussendoor\Billink\Credit\CreditList;

class Credit extends Endpoint
{
    protected $creditList;
    protected $customer;
    protected $action = 'Credit';

    public function __construct(CreditList $list)
    {
        $this->creditList = $list;
    }

    /**
     * Set the API action to 'credit', which is the default.
     * @return $this
     */
    public function asCredit()
    {
        $this->action = 'Credit';

        return $this;
    }

    /**
     * Set the API action to 'refund' instead of the default 'credit'.
     * @return $this
     */
    public function asRefund()
    {
        $this->action = 'Refund';

        return $this;
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getUrlEndpoint()
    {
        return '/v1/client/credit';
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
            'INVOICES'      => $this->creditList->map(function (Model $item) {
                return new Item($item->toArray());
            })->all()
        ];
    }
}
