<?php

namespace Tussendoor\Billink\Gateway;

use WC_Tax;
use WC_Cart;
use Tussendoor\Billink\Order\Fee;

class CartFeeProcessor
{
    protected $fee;

    public function __construct(Fee $fee)
    {
        $this->fee = $fee;
    }

    public function __invoke(WC_Cart $cart)
    {
        return $this->process($cart);
    }

    /**
     * Add the Billink fee to the cart.
     * @param  \WC_Cart $cart
     * @return bool
     */
    public function process(WC_Cart $cart)
    {
        return (bool) $this->addFee($cart);
    }

    /**
     * Remove the Billink fee from the WC_Cart.
     * @param  \WC_Cart $cart
     * @return bool
     */
    public function rollback(WC_Cart $cart)
    {
        $fees = $cart->fees_api()->get_fees();

        // If our costs are found, unset them from the array.
        if (isset($fees[$this->fee->name])) {
            unset($fees[$this->fee->name]);
        }

        // Remove all existing fees from the cart.
        $cart->fees_api()->remove_all_fees();

        // Re-add the fees to the cart, but this time excluding our costs.
        if (!empty($fees)) {
            foreach ($fees as $key => $fee) {
                $fee = is_object($fee) ? get_object_vars($fee) : $fee;
                $cart->add_fee($fee['name'], $fee['amount'], $fee['taxable'], $fee['tax_class']);
            }
        }

        return true;
    }

    /**
     * Add the Fee to the WC_Cart instance.
     * @param \WC_Cart $cart
     */
    protected function addFee($cart)
    {
        $feeAmount = $this->getFeeAmount();

        $fee = $cart->fees_api()->add_fee([
            'name'      => $this->fee->name,
            'amount'    => $feeAmount,
            'total'     => $feeAmount,
            'tax_class' => '',
            'taxable'   => true,
        ]);

        return is_wp_error($fee) === false;
    }

    /**
     * Return the fee amount, depending on some WooCommerce settings.
     * @return float
     */
    protected function getFeeAmount()
    {
        $feeAmount = $this->fee->amount;

        if (!wc_prices_include_tax()) {
            return $feeAmount;
        }

        $rates = WC_Tax::get_base_tax_rates();
        $bRate = reset($rates);

        if (empty($bRate) || !isset($bRate['rate'])) {
            return $feeAmount;
        }

        $feeAmount = $this->fee->amount / (($bRate['rate'] / 100) + 1);

        return $feeAmount;
    }
}
