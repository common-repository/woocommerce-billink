<?php

namespace Tussendoor\Billink\Order;

use Tussendoor\Billink\App;
use InvalidArgumentException;
use Tussendoor\Billink\Model;
use Tussendoor\Billink\Contracts\Formattable;
use Tussendoor\Billink\Contracts\Normalizable;

class OrderLine extends Model implements Normalizable, Formattable
{
    /**
     * Set the quantity attribute on the model. Checks if the given quantity is valid.
     * @param  int                      $quantity
     * @throws InvalidArgumentException When the quantity is invalid (outside 0 or PHP_INT_MAX)
     */
    public function setQuantityAttribute($quantity)
    {
        if (!is_numeric($quantity) || $quantity < 0 || $quantity > PHP_INT_MAX) {
            throw new InvalidArgumentException("Quantity must be an integer and must be between 0 and " . PHP_INT_MAX);
        }

        return $quantity;
    }

    /**
     * If no price including tax was found, try to calculate it from the price
     * excluding tax and the tax percentage.
     * @param  float|null $original
     * @return float|null
     */
    public function getPriceInclAttribute($original)
    {
        if (!is_null($original)) {
            return round($original, 2);
        }

        $excl = $this->getAttributeFromArray('priceExcl');
        $vat = $this->getAttributeFromArray('vat');

        if (!is_null($excl) && !is_null($vat)) {
            // When the vat percentage is equal to 0, return the price excluding tax.
            return round(($vat <= 0 ? $excl : $excl + (($excl / 100) * $vat)), 2);
        }

        return null;
    }

    /**
     * If no price excluding tax was found, try to calculate it from the price
     * excluding tax and the tax percentage.
     * @param  float|null $original
     * @return float|null
     */
    public function getPriceExclAttribute($original)
    {
        if (!is_null($original)) {
            return round($original, 2);
        }

        $incl = $this->getAttributeFromArray('priceIncl');
        $vat = $this->getAttributeFromArray('vat');

        if (!is_null($incl) && !is_null($vat)) {
            // When the vat percentage is equal to 0, return the price including tax.
            return round(($vat <= 0 ? $incl : $incl / (($vat / 100) + 1)), 2);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public static function getRootName()
    {
        return 'ITEM';
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
            'CODE'          => $this->code,
            'DESCRIPTION'   => $this->description,
            'ORDERQUANTITY' => $this->quantity,
            'PRICEINCL'     => $this->priceIncl,
            'PRICEEXCL'     => $this->priceExcl,
            'BTW'           => $this->vat,
        ], function ($item) {
            return $item !== '' && $item !== null;
        });
    }
}
