<?php

namespace Tussendoor\Billink\Converters;

use WC_Order;
use WC_Order_Item_Fee;
use WC_Order_Item_Product;
use Tussendoor\Billink\Order\OrderLine;
use Tussendoor\Billink\Order\OrderLineList;

/**
 * Convert all items in a WC_Order instance to a OrderLineList instance with
 * OrderLine instances.
 * @todo Needs refactoring
 */
class OrderLines
{
    protected $order;
    protected $orderLineList;

    /**
     * Set the WC_Order we need to parse and create a new instance of OrderLineList.
     * @param \WC_Order $order
     */
    public function __construct(WC_Order $order)
    {
        $this->order = $order;
        $this->orderLineList = new OrderLineList();
    }

    /**
     * Parse the items in the order into our OrderLine instances. Also converts fees
     * and shipping costs.
     * @return \Tussendoor\Billink\Order\OrderLineList
     */
    public function parse()
    {
        foreach ($this->order->get_items() as $item) {
            if (empty($item)) {
                continue;
            }

            $this->orderLineList->append($this->parseItem($item));
        }

        foreach ($this->getOrderFees() as $fee) {
            if (empty($fee)) {
                continue;
            }

            $this->orderLineList->append($fee);
        }

        foreach ($this->getShippingCosts() as $shippingCost) {
            if (empty($shippingCost)) {
                continue;
            }

            $this->orderLineList->append($shippingCost);
        }

        foreach ($this->getCouponCodes() as $coupon) {
            if (empty($coupon)) {
                continue;
            }

            $this->orderLineList->append($coupon);
        }

        // if ($this->orderHasDiscounts()) {
        //     $this->orderLineList->append($this->getDiscounts());
        // }

        return $this->orderLineList;
    }

    /**
     * Turn an order item info a OrderLine instance.
     * @param  \WC_Order_Item                      $item
     * @return \Tussendoor\Billink\Order\OrderLine
     */
    protected function parseItem($item)
    {
        list($priceIncl, $priceExcl) = $this->getItemPrice($item);

        $orderLine = new OrderLine();
        $orderLine->code = $item->get_id(); // Maybe apply filtering?
        $orderLine->description = $item->get_name();
        $orderLine->quantity = $item->get_quantity();
        $orderLine->priceIncl = $priceIncl;
        $orderLine->priceExcl = $priceExcl;
        $orderLine->vat = $this->getItemTaxPercentage($item);

        return $orderLine;
    }

    /**
     * Get the fees from a WooCommerce order and turn them into OrderLine instances.
     * @return \Generator|\Tussendoor\Billink\Order\OrderLine[]
     */
    protected function getOrderFees()
    {
        $fees = $this->order->get_fees();

        if (empty($fees)) {
            yield;
        }

        foreach ($fees as $fee) {
            if ($fee instanceof WC_Order_Item_Fee === false) {
                continue;
            }

            $orderLine = new OrderLine();
            $orderLine->description = $fee->get_name();
            $orderLine->quantity = $fee->get_quantity();
            $orderLine->priceExcl = $fee->get_total();
            $orderLine->vat = 0;

            if ($fee->get_tax_status() == 'taxable' && $fee->get_total_tax() != '0') {
                $rates = \WC_Tax::get_rates($fee->get_tax_class());
                $orderLine->vat = !empty($rates) ? reset($rates)['rate'] : 0;
            }

            yield $orderLine;
        }
    }

    /**
     * Get the coupons from a WooCommerce order and turn them into OrderLine instances.
     * @return \Generator|\Tussendoor\Billink\Order\OrderLine[]
     */
    protected function getCouponCodes()
    {
        $usedCoupons = $this->order->get_items('coupon');
        if (empty($usedCoupons)) {
            yield;
        }

        yield;

        foreach ($usedCoupons as $coupon) {
            $incl = round($coupon->get_discount('edit') + $coupon->get_discount_tax('edit'), 2);
            // $excl = $coupon->get_discount('edit');

            $discountLine = sprintf(
                __('Coupon "%s" (€%s discount)', 'woocommerce-wefact'),
                $coupon->get_code(),
                $incl
            );

            $orderLine = new OrderLine();
            $orderLine->code = $coupon->get_id();
            $orderLine->description = $discountLine;
            $orderLine->quantity = 1;
            $orderLine->priceIncl = 0;
            $orderLine->priceExcl = 0;
            $orderLine->vat = 0;

            // $orderLine->priceIncl = $incl;
            // $orderLine->priceExcl = $coupon->get_discount('edit');
            // if (abs(($incl - $excl) / $excl) < 0.00001) {
            //     $orderLine->vat = 0;
            // } else {
            //     $orderLine->vat = (($incl - $excl) / $excl) * 100;
            // }

            yield $orderLine;
        }
    }

    /**
     * Get the shipping costs from a WC_Order and turn them into an OrderLine instance.
     * @return \Generator|\Tussendoor\Billink\Order\OrderLine[]
     */
    protected function getShippingCosts()
    {
        if ($this->order->calculate_shipping() == 0) {
            yield;
        }

        foreach ($this->order->get_shipping_methods() as $shippingCost) {
            $orderLine = new OrderLine();
            $orderLine->description = $shippingCost->get_name();
            $orderLine->quantity = 1;
            $orderLine->priceExcl = (float) $shippingCost->get_total();
            $orderLine->priceIncl = ((float) $shippingCost->get_total() + (float) $shippingCost->get_total_tax());
            $orderLine->vat = $this->getItemTaxPercentage($shippingCost);

            yield $orderLine;
        }
    }

    /**
     * Verify wether or not the order has any discounts.
     * @return bool
     */
    protected function orderHasDiscounts()
    {
        return $this->order->get_discount_total() > 0;
    }

    /**
     * Return an OrderLine instance containing all discounts.
     * @return \Tussendoor\Billink\Order\OrderLine
     */
    protected function getDiscounts()
    {
        $orderLine = new OrderLine();
        $orderLine->description = __('Discounts', 'woocommerce-gateway-billink');
        $orderLine->quantity = 1;
        $orderLine->priceExcl = -(float) $this->order->get_discount_total();
        $orderLine->priceIncl = -((float) $this->order->get_discount_total() + (float) $this->order->get_discount_tax());
        $orderLine->vat = 0;

        return $orderLine;
    }


    /**
     * Calculate the price including and excluding VAT for the given order item.
     * @param  \WC_Order_Item $item
     * @return array          Use list($priceIncl, $priceExcl)
     */
    protected function getItemPrice($item)
    {
        // WC_Order::get_items() returns an array of WC_Order_Item instances, but this class
        // does not have the 'get_total', 'get_total_tax' or 'get_taxes' methods available.
        // Some child classes (like WC_Order_Item_Product) however do have these methods.
        // WC_Order::get_items() will usually return WC_Order_Item_Product instances,
        // but since the return type in WooCommerce does not reflect that, we'll add
        // a few method checks.
        if (
            method_exists($item, 'get_total') === false ||
            method_exists($item, 'get_taxes') === false ||
            method_exists($item, 'get_total_tax') === false
        ) {
            return [0, 0];
        }

        $price = ($item->get_total() / $item->get_quantity());
        $priceIncl = $price + ($item->get_total_tax() / $item->get_quantity());

        return [$priceIncl, $price];
    }

    /**
     * Get the tax percentage for the given item.
     * @param  \WC_Order_Item $item
     * @return float
     * @todo Maybe move to separate class
     */
    protected function getItemTaxPercentage($item)
    {
        global $wpdb;
        $taxes = $item->get_taxes();

        if (!isset($taxes['subtotal'])) {
            $taxes['subtotal'] = isset($taxes['total']) ? $taxes['total'] : 0;
        }

        foreach ($taxes['subtotal'] as $rateId => $tax) {
            if (empty($tax)) {
                continue;
            }

            $taxCalulationMethod = get_option('woocommerce_tax_based_on');
            $taxRate = $wpdb->get_row($this->getTaxQuery($taxCalulationMethod, $rateId));

            if (empty($taxRate)) {
                continue;
            }

            return (float) $taxRate->tax_rate;
        }

        return 0;
    }

    /**
     * Get the SQL query that resolves the tax rate for the given type and rateId.
     * @param  string $type
     * @param  int    $rateId
     * @return string
     */
    protected function getTaxQuery($type, $rateId)
    {
        global $wpdb;

        switch ($type) {
            case 'shipping':
                return $wpdb->prepare("
                    SELECT * FROM `{$wpdb->prefix}woocommerce_tax_rates`
                    WHERE tax_rate_id = %d
                    AND (tax_rate_country = %s OR tax_rate_country = '')
                ", $rateId, $this->order->get_shipping_country());
            case 'billing':
                return $wpdb->prepare("
                    SELECT * FROM `{$wpdb->prefix}woocommerce_tax_rates`
                    WHERE tax_rate_id = %d
                    AND (tax_rate_country = %s OR tax_rate_country = '')
                ", $rateId, $this->order->get_billing_country());
            case 'base':
                return $wpdb->prepare("
                    SELECT * FROM `{$wpdb->prefix}woocommerce_tax_rates`
                    WHERE tax_rate_id = %d", $rateId);
        }

        return $wpdb->prepare("
            SELECT * FROM `{$wpdb->prefix}woocommerce_tax_rates`
            WHERE tax_rate_id = %d
        ", $rateId);
    }
}
