<?php

namespace Tussendoor\Billink\Gateway;

use DateTime;
use Tussendoor\Billink\Helpers\ParameterBag;
use Tussendoor\Billink\Exceptions\ValidationError;

class FieldValidationProcessor
{
    public function __invoke(ParameterBag $request)
    {
        return $this->process($request);
    }

    /**
     * Process and validate the given ParamterBag that contains the request variables. Checks if
     * the terms are accepted and validates the birthdate for private orders and the chamber of
     * commerce number for business orders.
     * @param  ParameterBag    $request
     * @return mixed
     * @throws ValidationError
     */
    public function process(ParameterBag $request)
    {
        if ($request->getBoolean('billink_accept') === false) {
            throw new ValidationError(__('You must accept the Billink terms.', 'woocommerce-gateway-billink'));
        }

        $isBusiness = $request->has('billing_company') && $request->isNotEmpty('billing_company');

        if ($isBusiness) {
            return $this->processBusiness($request);
        }

        return $this->processPrivate($request);
    }

    /**
     * Process the given request as a business. Checks if the chamber of commerce number is set.
     * @param  ParameterBag    $request
     * @return string
     * @throws ValidationError
     */
    protected function processBusiness($request)
    {
        if (
            $request->has('billink_chamber_of_commerce') === false ||
            $request->isEmpty('billink_chamber_of_commerce')
        ) {
            throw new ValidationError(__('Chamber of Commerce number is required.', 'woocommerce-gateway-billink'));
        }

        return sanitize_text_field($request->get('billink_chamber_of_commerce'));
    }

    /**
     * Process the given request as a private person. Validates the given birthdate if it exists.
     * @param  ParameterBag    $request
     * @return DateTime
     * @throws ValidationError
     */
    protected function processPrivate($request)
    {
        if ($request->has('billink_birthdate') === false || $request->isEmpty('billink_birthdate')) {
            throw new ValidationError(__('Birthdate is required.', 'woocommerce-gateway-billink'));
        }

        // Match the date input against the required format (dd-mm-YYYY).
        $matched = preg_match(
            '/^(?:(?:0?[1-9])|(?:[1-2]\d)|(?:3[0-1]))-(?:(?:0?[1-9])|(?:1[0-2]))-[1-2]\d{3}$/',
            $request->get('billink_birthdate'),
            $matches
        );

        // If no match was found there was probably an input error on the user' end.
        if ($matched !== 1) {
            throw new ValidationError(__('Birthdate must be formatted as dd-mm-yyyy.', 'woocommerce-gateway-billink'));
        }

        // Create a new DateTime instance from the given birthdate. If createFromFormat
        // returns false or the d-m-Y format is not equal to the given birthdate, the
        // date should be considered invalid.
        $birthdate = DateTime::createFromFormat('d-m-Y', $matches[0]);
        if (
            $birthdate === false ||
            $birthdate->format('d-m-Y') !== $request->get('billink_birthdate')
        ) {
            throw new ValidationError(__('Birthdate must be formatted as dd-mm-yyyy.', 'woocommerce-gateway-billink'));
        }

        return $birthdate;
    }
}
