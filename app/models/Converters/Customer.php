<?php

namespace Tussendoor\Billink\Converters;

use WC_Customer;
use WC_Geolocation;
use Tussendoor\Billink\Customer\Customer as CustomerModel;

/**
 * Convert a WC_Customer to a Billink Customer
 */
class Customer
{
    protected $customer;

    public function __construct(WC_Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * Parse the internal WC_Customer instance into our own Customer model.
     * @return \Tussendoor\Billink\Customer\Customer
     */
    public function parse()
    {
        $billing = (new Address($this->customer))->parse();
        if (empty($_POST['ship_to_different_address']) || $_POST['ship_to_different_address'] != '1') {
            $shipping = $billing;
        } else {
            $shipping = (new ShippingAddress($this->customer))->parse();
        }

        $customer = new CustomerModel();
        $customer->firstname = $this->customer->get_billing_first_name();
        $customer->lastname = $this->customer->get_billing_last_name();
        $customer->email = $this->customer->get_billing_email();
        $customer->phonenumber = $this->customer->get_billing_phone();

        $customer->ip = WC_Geolocation::get_ip_address();
        $customer->address = $billing;
        $customer->shippingAddress = $shipping;
        $customer->setBusiness($this->customerIsCompany());
        $customer->sex = 'O';

        // $customer->vatNumber
        // $customer->debtorNumber

        if ($this->customerIsCompany()) {
            return $this->addCompanyDetails($customer);
        }

        // $customer->birthdate = $this->customer->get_birthdate();

        return $customer;
    }

    /**
     * Wether or not the current customer should be considered as a company
     * @return bool
     */
    protected function customerIsCompany()
    {
        return $this->customer->get_billing_company() != '';
    }

    /**
     * Add some properties to the given Customer instance, relating to companies.
     * @param \Tussendoor\Billink\Customer\Customer $customer
     */
    protected function addCompanyDetails($customer)
    {
        $customer->setBusiness(true);
        $customer->companyName = $this->customer->get_billing_company();
        // $customer->chamberOfCommerce = $this->customer->get_coc_value();

        return $customer;
    }
}
