<?php

namespace Tussendoor\Billink\Order;

use DateTime;
use WC_Order;
use Tussendoor\Billink\App;
use Tussendoor\Billink\Model;
use Tussendoor\Billink\Contracts\Formattable;
use Tussendoor\Billink\Contracts\Normalizable;

class Order extends Model implements Normalizable, Formattable
{
    protected $woocommerceOrder;

    public function total()
    {
        return $this->woocommerce()->get_total();
    }

    public function setDateAttribute(DateTime $date)
    {
        return $date;
    }

    public function setItemsAttribute(OrderLineList $list)
    {
        return $list;
    }

    public function setCurrencyAttribute(Currency $currency)
    {
        return $currency;
    }

    public function getCurrencyAttribute($original)
    {
        if ($original && $original instanceof Currency) {
            return $original;
        }

        try {
            return Currency::byName(get_woocommerce_currency());
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    public function setValidateOrderAttribute($validate)
    {
        return (bool) $validate;
    }

    public function setWooCommerceOrder(WC_Order $order)
    {
        $this->woocommerceOrder = $order;

        return $this;
    }

    public function woocommerce()
    {
        return $this->woocommerceOrder;
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public static function getRootName()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     * @param  string $format
     * @return string
     */
    public function serialize($format = 'xml')
    {
        return App::get('serializer')->serialize($this, $format);
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function normalize()
    {
        return array_filter([
            'ORDERAMOUNT'           => $this->total(),
            'ORDERITEMS'            => $this->items->all(),
            'ORDERNUMBER'           => $this->orderNumber,
            'CURRENCY'              => $this->currency->getValue(),
            'ADDITIONALTEXT'        => $this->additionalText,
            'DATE'                  => $this->date->format('d-m-Y'),
            'TRACKANDTRACE'         => $this->trackAndTrace,
        ], function ($item) {
            return $item !== '' && $item !== null;
        });
    }
}
