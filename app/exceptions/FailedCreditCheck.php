<?php

namespace Tussendoor\Billink\Exceptions;

class FailedCreditCheck extends UserError
{
    protected $error_messages;

    public function __construct($message, $code = 0, Throwable $previous = null) {

        $this->setErrorMessages();
        $message = $this->translateErrorMessage($message);

        $this->message = $message;
        $this->code = $code;
        $this->previous = $previous;
    }

    public function setErrorMessages()
    {
        $this->error_messages = [
            'Amount is bigger than allowed' => __('Choose a different payment method. The order amount is higher than allowed (max 10.000,- excl. VAT)', 'woocommerce-gateway-billink'),
            'Delivery address not allowed' => __('Payment through Billink not possible. Delivery address does not match billing address.', 'woocommerce-gateway-billink'),
        ];
    }

    public function translateErrorMessage($message)
    {
        if (array_key_exists($message, $this->error_messages)) {
            $message = $this->error_messages[$message];
        }

        return $message;
    }
}
