<?php

namespace Tussendoor\Billink\Helpers;

use Tussendoor\Billink\Exceptions\InvalidAddress;

class AddressSplitter
{

    /**
     * The original address string.
     * @var string
     */
    protected $address;

    /**
     * Inject the address string that needs to be split.
     * @param string $addressString
     */
    public function __construct($addressString)
    {
        $this->address = $addressString;
    }

    /**
     * Split the address string into a streetname, housenumber and an extension.
     * @return array
     * @throws \Tussendoor\Billink\Exceptions\InvalidAddress
     */
    public function split()
    {
        $addressFields = explode(' ', trim($this->address));

        switch (count($addressFields)) {
            case 1:
                return $this->invalidAddress();
            case 2:
                return $this->parseDualAddressFields($addressFields);
            case 3:
                return $this->parseTripleAddressFields($addressFields);
            default:
                return $this->parseDynamicAddressFields($addressFields);
        }
    }

    /**
     * Parse a dual (two-part) address string. Usually this means a streetname and
     * housenumber combination, but sometimes the housenumber will contain an
     * extension. For example: Streetname 3-B.
     * @param  array $addressFields
     * @return array
     * @throws \Tussendoor\Billink\Exceptions\InvalidAddress
     */
    protected function parseDualAddressFields($addressFields)
    {   
        list($street, $houseNumber) = $addressFields;

        if ($this->containsOnlyNumbers($houseNumber)) {
            return $this->formatAddress($street, $houseNumber);
        }

        $numberParts = [];
        if ($this->containsHyphenNotAtStart($houseNumber)) {
            $numberParts = explode('-', $houseNumber);
        } elseif ($this->isAlphaNumeric($houseNumber)) {
            $numberParts = preg_split('#(?<=\d)(?=[a-z])#i', $houseNumber);
        }

        if (!empty($numberParts)) {
            list($houseNumber, $extension) = $this->formatNumberParts($numberParts);

            return $this->formatAddress($street, $houseNumber, $extension);
        }

        return $this->invalidAddress();
    }

    /**
     * Parse a triple part address string. Usually this means a streetname with a
     * space and a housenumber combination. For example: Street Name 3-B. But it
     * might also be a street, housenumber and separate suffix like Streetname 3 B.
     * @param  array $addressFields
     * @return array
     * @throws \Tussendoor\Billink\Exceptions\InvalidAddress
     */
    protected function parseTripleAddressFields($addressFields)
    {
        list($street, $houseNumber, $optionalSuffix) = $addressFields;
        if ($this->isAlphabetic($houseNumber)) {
            return $this->parseDualAddressFields(
                [$street . ' ' . $houseNumber, $optionalSuffix]
            );
        }

        return $this->formatAddress($street, $houseNumber, $optionalSuffix);
    }

    /**
     * Parse an unknown length of address fields.
     * @param  array $addressFields
     * @return array
     */
    protected function parseDynamicAddressFields($addressFields)
    {
        [$street, $remaining] = $this->flattenStreetField($addressFields);

        if (empty($street) || count($remaining) <= 0) {
            return $this->invalidAddress();
        }

        // By default we'll assume there is a suffix, but we will default to null
        // if there is none. However, if the $remaining array contains three or
        // more items, assume the first entry will always be the housenumber.
        // Everything after it will be flattened and merged into one suffix.
        $suffix = (isset($remaining[1]) ? $remaining[1] : null);
        if (count($remaining) > 2) {
            $suffix = implode(' ', array_slice($remaining, 1));
        }

        return $this->formatAddress($street, $remaining[0], $suffix);
    }

    /**
     * For the given array of address fields, merge all alphabetic entries into
     * one string. The assumption here is that streetnames never contain any
     * numbers. This will surely never bite us in the ass again, because
     * this logic does not contain any flaws, now does it? :D
     * @param  array $addressFields
     * @return array                A street entry and the remaining fields.
     */
    protected function flattenStreetField($addressFields)
    {
        $street = [];

        foreach ($addressFields as $index => $field) {
            // Return immediately if the given field is not fully alphabetic.
            if (
                !$this->isAlphabetic($field) &&
                // Except for streets starting with a count, e.g.: 3e Dwarsstraat
                ($index !== 0 && count($addressFields) > 2)
            ) {
                return [implode(' ', $street), array_slice($addressFields, $index)];
            }

            $street[] = $field;
        }

        return [implode(' ', $street), array_slice($addressFields, $index)];
    }

    /**
     * Format the given house number parts into a separate house number and extension.
     * @param  array $parts
     * @return array
     */
    protected function formatNumberParts($parts)
    {
        return [
            // House number
            reset($parts),
            // Extension
            count($parts) > 2 ? implode(' ', array_slice($parts, 1)) : end($parts)
        ];
    }

    /**
     * Checks if the given string is alpha numeric. This means it contains (at least)
     * one alphabetic and one numeric character.
     * @param  string $string
     * @return bool
     */
    protected function isAlphaNumeric($string)
    {
        return ctype_alnum($string)
            && !$this->isAlphabetic($string)
            && !$this->containsOnlyNumbers($string);
    }

    protected function isAlphabetic($string)
    {
        return ctype_alpha($string) || preg_match('/\p{P}|\p{S}/', $string) === 1;
    }

    /**
     * Wether or not the given string contains a hyphen and is not found at the start.
     * @param  string $string
     * @return bool
     */
    protected function containsHyphenNotAtStart($string)
    {
        $position = strpos($string, '-');

        return $position !== false && $position > 0;
    }

    /**
     * Check if the given string contains only numeric characters.
     * @param  string $string
     * @return bool
     */
    protected function containsOnlyNumbers($string)
    {
        return ctype_digit((string) $string);
    }

    /**
     * Trim away useless whitespace and replace the suffix with an empty string
     * if it was not provided as method parameter.
     * @param  string $street
     * @param  string|int $houseNumber
     * @param  string $suffix
     * @return array
     */
    protected function formatAddress($street, $houseNumber, $suffix = null)
    {
        $suffix = $suffix ?? '';
        return [
            trim($street), trim($houseNumber), trim($suffix)
        ];
    }

    /**
     * Throws an InvalidAddress exception.
     * @return void
     * @throws \Tussendoor\Billink\Exceptions\InvalidAddress
     */
    protected function invalidAddress()
    {
        throw new InvalidAddress(__(
            'Your house number cannot be determined. Fill in your address + house number, for example Streetname 12.',
            'woocommerce-gateway-billink'
        ));
    }
}
