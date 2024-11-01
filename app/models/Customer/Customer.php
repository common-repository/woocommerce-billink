<?php

namespace Tussendoor\Billink\Customer;

use Tussendoor\Billink\App;
use Tussendoor\Billink\Model;
use Tussendoor\Billink\Contracts\Formattable;
use Tussendoor\Billink\Contracts\Normalizable;

class Customer extends Model implements Normalizable, Formattable
{
    /**
     * Wether or not the customer should be considered a business customer. Sets the 'type'
     * attribute.
     * @param bool $isBusiness
     */
    public function setBusiness($isBusiness)
    {
        return $this->setAttribute('type', ((bool) $isBusiness ? 'B' : 'P'));
    }

    /**
     * Wether or not the customer is considered a business.
     * @return bool
     */
    public function isBusiness()
    {
        return $this->getAttribute('type') === 'B';
    }

    /**
     * Wether or not the customer should be considered highrisk.
     * @param bool $isHighrisk
     */
    public function setHighrisk($isHighrisk)
    {
        return $this->highrisk = ((bool) $isHighrisk ? 1 : 0);
    }

    /**
     * Wether nor not the customer is considered highrisk.
     * @return bool
     */
    public function isHighrisk()
    {
        return $this->getAttribute('highrisk') == 1;
    }

    /**
     * Set the 'address' attribute. Requires an Address instance.
     * @param Address $address
     */
    public function setAddressAttribute(Address $address)
    {
        return $address;
    }

    /**
     * Set the 'shippingAddress' attribute. Requires an Address instance.
     * @param Address $address
     */
    public function setShippingAddressAttribute(Address $address)
    {
        return $address;
    }

    /**
     * Return the initials attribute from the model. If it does not exist, generate
     * it from the firstname attribute on the model.
     * @return string
     */
    public function getInitialsAttribute()
    {
        $original = $this->getAttributeFromArray('initials');
        if (!empty($original)) {
            return $original;
        }

        return strtoupper(implode('', array_map(function ($name) {
            return substr($name, 0, 1);
        }, explode(' ', $this->firstname))));
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
        return array_filter(array_merge(
            [
                'TYPE'              => $this->type,
                'FIRSTNAME'         => \billink_xml_safe_value($this->firstname),
                'LASTNAME'          => \billink_xml_safe_value($this->lastname),
                'INITIALS'          => $this->initials,
                'PHONENUMBER'       => $this->phonenumber,
                'BIRTHDATE'         => $this->birthdate,
                'SEX'               => $this->sex,
                'EMAIL'             => \billink_xml_safe_value($this->email),
                'COMPANYNAME'       => \billink_xml_safe_value($this->companyName),
                'CHAMBEROFCOMMERCE' => $this->chamberOfCommerce,
                'IP'                => $this->ip,
                'HIGHRISK'          => $this->highrisk,
                'BACKDOOR'          => $this->backdoor,
                'VATNUMBER'         => $this->vatNumber,
                'DEBTORNUMBER'      => $this->debtorNumber,
                'CHECKUUID'         => $this->checkUuid,
            ],
            $this->normalizeAddress($this->address),
            $this->normalizeShippingAddress($this->shippingAddress)
        ), function ($item) {
            return $item !== '' && $item !== null;
        });
    }

    /**
     * Normalize a given Address instance
     * @param  \Tussendoor\Billink\Customer\Address|null $address
     * @return array
     */
    protected function normalizeAddress($address)
    {
        return $address ? [
            'HOUSENUMBER'       => $address->number,
            'HOUSEEXTENSION'    => $address->extension,
            'POSTALCODE'        => $address->postalCode,
            'STREET'            => \billink_xml_safe_value($address->street),
            'CITY'              => \billink_xml_safe_value($address->city),
            'COUNTRYCODE'       => \billink_xml_safe_value($address->country),
        ] : [];
    }

    /**
     * Normalize a given Address instance as the shipping address
     * @param  \Tussendoor\Billink\Customer\Address|null $shipping
     * @return array
     */
    protected function normalizeShippingAddress($shipping)
    {
        return $shipping ? [
            'DELIVERY_HOUSENUMBER'      => $shipping->number,
            'DELIVERY_HOUSEEXTENSION'   => $shipping->extension,
            'DELIVERY_POSTALCODE'       => $shipping->postalCode,
            'DELIVERYSTREET'            => \billink_xml_safe_value($shipping->street),
            'DELIVERYCITY'              => \billink_xml_safe_value($shipping->city),
            'DELIVERYCOUNTRYCODE'       => \billink_xml_safe_value($shipping->country),

            'DELIVERYADDRESSCOMPANYNAME' => \billink_xml_safe_value($shipping->companyName),
            'DELIVERYADDRESSFIRSTNAME'  => \billink_xml_safe_value($shipping->firstname),
            'DELIVERYADDRESSLASTNAME'   => \billink_xml_safe_value($shipping->lastname),
        ] : [];
    }
}
