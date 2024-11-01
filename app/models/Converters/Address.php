<?php

namespace Tussendoor\Billink\Converters;

use WC_Customer;
use Tussendoor\Billink\Helpers\AddressSplitter;
use Tussendoor\Billink\Customer\Address as AddressModel;

/**
 * Convert a WC_Customer to a Billink Address instance
 */
class Address
{
    protected $customer;

    public function __construct(WC_Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * Parse a WC_Customer instance and return a Customer\Address instance
     * @return \Tussendoor\Billink\Customer\Address
     * @throws \Tussendoor\Billink\Exceptions\InvalidAddress
     */
    public function parse()
    {
        /**
         * Filter: billink_address_converter_customer_billing_address
         * By default returns the get_billing_address_1() and get_billing_address_2() concatenated
         * Can be used to override the address string, for example when using a custom address field
         * 
         * @param string $address - The address string
         * @param WC_Customer $customer - The customer instance
         * @return string - The address string
         */
        $address = apply_filters('billink_address_converter', ($this->customer->get_billing_address_1() . ' ' . $this->customer->get_billing_address_2()), 'billing');

        $splitter = new AddressSplitter($address);
        list($street, $number, $extension) = $splitter->split();

        /**
         * Filter: billink_address_converter_customer_billing_address_data
         * Can be used to override the address data, for example when using a custom address field
         * 
         * @param array $addressModelData - The address data
         * @param WC_Customer $customer - The customer instance
         * @param string $address - The original address string
         * @param AddressSplitter $splitter - The address splitter instance
         * @return array - The address data
         */
        $addressModelData = apply_filters('billink_address_converter_customer_billing_address_data', [
            'street'        => $street,
            'number'        => $number,
            'extension'     => $extension,
            'postalCode'    => $this->customer->get_billing_postcode(),
            'city'          => $this->customer->get_billing_city(),
            'country'       => $this->customer->get_billing_country(),
        ], $this->customer, $address, $splitter);

        /**
         * Action: billink_save_custom_address
         * Can be used to update address data from the customer and current order 
         * with custom address fields
         * 
         * @param array $addressModelData - The address data
         */
        do_action('billink_save_custom_address', $addressModelData);

        return new AddressModel($addressModelData);
    }
}
