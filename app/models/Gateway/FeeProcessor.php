<?php

namespace Tussendoor\Billink\Gateway;

use WC_Tax;
use Tussendoor\Billink\Order\Fee;
use Tussendoor\Billink\Helpers\LegacyPaymentCosts;

class FeeProcessor
{
    protected $config = [];

    public function __construct($paymentCostConfig = [])
    {
        // Backward compatability with version <= 1
        if (is_string($paymentCostConfig)) {
            $legacyConfig = new LegacyPaymentCosts($paymentCostConfig);

            $paymentCostConfig = $legacyConfig->format();
        }

        $this->config = $paymentCostConfig;
    }

    /**
     * Generate a Fee instance for the given order total. Checks the settings for the rate.
     * @param  float                         $orderTotal
     * @return \Tussendoor\Billink\Order\Fee
     */
    public function process($orderTotal)
    {
        // Construct the base Fee instance.
        $fee = new Fee([
            'name'      => __('Billink payment costs', 'woocommerce-gateway-billink'),
            'amount'    => 0,
            'taxable'   => true,
            'vat'       => $this->getTaxRate(),
        ]);

        // Get the correct config entry from the given configs in the constructor.
        $config = $this->getConfig($orderTotal);
        if ($config === false) {
            return $fee;
        }

        if ($config['type'] === 'fixed') {
            $fee->amount = $config['value'];

            return $fee;
        }

        $fee->amount = ($orderTotal / 100) * $config['value'];

        return apply_filters('billink_fee_processed', $fee, $orderTotal, $config);
    }

    /**
     * Get the fee config that belongs to the given order total
     * @param  float       $orderTotal
     * @return array|false
     */
    protected function getConfig($orderTotal)
    {
        if (empty($this->config)) {
            return false;
        }

        foreach ($this->config as $config) {
            if ($this->customerIsInCountry($config['country']) === false) {
                continue;
            }

            if ($this->orderTotalIsInRange([$config['min'], $config['max']], $orderTotal) === false) {
                continue;
            }

            return $config;
        }

        return false;
    }

    /**
     * Checks if the order total is within the given range.
     * @param  array $range
     * @param  float $orderTotal
     * @return bool
     */
    protected function orderTotalIsInRange($range, $orderTotal)
    {
        list($min, $max) = $range;

        if ($max == '' && $orderTotal > $min) {
            return true;
        }

        return $orderTotal <= $max && $orderTotal >= $min;
    }

    /**
     * Checks if the WooCommerce customer is in the given countries list.
     * @param  array $countries
     * @return bool
     */
    protected function customerIsInCountry($countries)
    {
        global $woocommerce;

        if (is_string($countries)) {
            $countries = [$countries];
        }

        if (count($countries) === 1 && reset($countries) === '*') {
            return true;
        }

        return in_array($woocommerce->customer->get_billing_country(), $countries);
    }

    protected function getTaxRate()
    {
        $taxCalculationMethod = get_option('woocommerce_tax_based_on');

        if ($taxCalculationMethod === 'base') {
            $rates = WC_Tax::get_base_tax_rates();

            // Billink does not support compound tax rates.
            return empty($rates) ? 0 : reset($rates)['rate'];
        }

        global $woocommerce;

        $rates = WC_Tax::find_rates([
            'country' => $taxCalculationMethod == 'billing' ?
                $woocommerce->customer->get_billing_country() :
                $woocommerce->customer->get_shipping_country(),
        ]);

        return empty($rates) ? 0 : reset($rates)['rate'];
    }
}
