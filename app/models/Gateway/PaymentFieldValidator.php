<?php

namespace Tussendoor\Billink\Gateway;

use Exception;
use Throwable;

class PaymentFieldValidator
{
    /**
     * All of Billink' payment fields.
     * @var array
     */
    protected $paymentFields;

    /**
     * Supply a list of payment fields.
     * @param array $fields
     */
    public function __construct($fields)
    {
        $this->paymentFields = $fields;
    }

    /**
     * Validate the given payment fields. Unsets some fields if needed.
     * @return array
     */
    public function validate()
    {
        $checkoutFields = $this->getWooCommerceCheckoutFields();

        if (isset($checkoutFields['billing']['billing_phone'])) {
            unset($this->paymentFields['billing_phone']);
        }

        $this->sortPaymentFieldsByPriority();

        return $this->paymentFields;
    }

    /**
     * Return all WooCommerce checkout fields after filters have been applied.
     * @return array
     */
    protected function getWooCommerceCheckoutFields()
    {
        try {
            return \WC()->checkout()->get_checkout_fields();
        } catch (Throwable $e) {
            return [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Sort the current list of payment fields by priority.
     */
    protected function sortPaymentFieldsByPriority()
    {
        uasort($this->paymentFields, function ($itemA, $itemB) {
            if ($itemA['priority'] === $itemB['priority']) {
                return 0;
            }

            return ($itemA['priority'] < $itemB['priority']) ? -1 : 1;
        });
    }
}
