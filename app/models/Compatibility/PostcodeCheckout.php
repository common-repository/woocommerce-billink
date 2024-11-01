<?php

namespace Tussendoor\Billink\Compatibility;

use WC_Customer;
use Tussendoor\Billink\Helpers\ParameterBag;
use Tussendoor\Billink\Contracts\PluginCompatible;

/**
 * Add compatibility for the 'Postcode Checkout - WooCommerce address validation'
 * plugin, which alters some of the checkout fields.
 */
class PostcodeCheckout implements PluginCompatible
{
    public function register()
    {
        add_filter('billink_request_data', [$this, 'mergeRequestData'], 10, 1);
        add_filter('billink_customer_data', [$this, 'mergeCustomerData'], 10, 2);
    }

    public function mergeRequestData(ParameterBag $request)
    {
        if ($request->has('billing_street_name')) {
            $request->set('billing_address_1', sprintf(
                '%s %s %s',
                $request->get('billing_street_name', ''),
                $request->get('billing_house_number', ''),
                $request->get('billing_house_number_suffix', '')
            ));
        }

        return $request;
    }

    public function mergeCustomerData(WC_Customer $customer, ParameterBag $request)
    {
        if ($request->has('billing_street_name')) {
            $customer->set_billing_address(sprintf(
                '%s %s %s',
                $request->get('billing_street_name', ''),
                $request->get('billing_house_number', ''),
                $request->get('billing_house_number_suffix', '')
            ));
        }

        if (
            $customer->get_shipping_address() == '' &&
            $request->has('billing_street_name')
        ) {
            $customer->set_shipping_address(sprintf(
                '%s %s %s',
                $request->get('billing_street_name', ''),
                $request->get('billing_house_number', ''),
                $request->get('billing_house_number_suffix', '')
            ));
        }

        return $customer;
    }
}
