<?php

namespace Tussendoor\Billink\Gateway;

use Exception;
use Throwable;
use Tussendoor\Billink\App;
use Tussendoor\Billink\Helpers\Log;
use Tussendoor\Billink\Order\Order;
use Tussendoor\Http\RequestException;
use Tussendoor\Billink\Customer\Customer;
use Tussendoor\Billink\Exceptions\FailedCreditCheck;
use Tussendoor\Billink\Endpoint\Check as CheckEndpoint;

class CheckProcessor
{
    protected $exceptionMessage;

    public function __invoke(Order $order, Customer $customer)
    {
        return $this->process($order, $customer);
    }

    /**
     * Set a custom exception message for when the check has failed.
     * @param string $message
     */
    public function setExceptionMessage($message)
    {
        $this->exceptionMessage = $message;

        return $this;
    }

    /**
     * Get the exception message used when the check has failed.
     * @return string
     */
    public function getExceptionMessage()
    {
        return $this->exceptionMessage ?
            $this->exceptionMessage :
            __('Unfortunately, ordering through Billink is not possible.', 'woocommerce-billink-gateway');
    }

    /**
     * Execute a Check API request for the given Order and Customer instance.
     * @param  \Tussendoor\Billink\Order\Order                  $order
     * @param  \Tussendoor\Billink\Customer\Customer            $customer
     * @return string                                           A UUID used for creating an order in Billink
     * @throws \Tussendoor\Billink\Exceptions\FailedCreditCheck In case the credit check failed
     * @throws \Tussendoor\Http\RequestException                In case where a HTTP error was encountered
     * @throws \Exception                                       In case an unknown error was encountered
     */
    public function process(Order $order, Customer $customer)
    {
        // Use the Customer instance on the Check endpoint class and inject the
        // AuthenticationHeader into it.
        $checkEndpoint = new CheckEndpoint($customer, $order);
        $checkEndpoint->setAuthenticationHeader(App::get('auth.header'))
            ->setWorkflowNumber($customer->workflowNumber);

        Log::info("Sending 'Check' request", ['request' => $checkEndpoint->serialize()]);

        // Build the HTTP request through our HTTP client and serialize the
        // Check instance into XML by calling the serialize() method.
        try {
            $response = App::get('http.client')
                ->post($checkEndpoint->getUrlEndpoint(), $checkEndpoint->serialize())
                ->send();

            $checkResponse = App::get('serializer')
                ->unserialize($response->getBody(), CheckEndpoint::class, 'xml');
        } catch (RequestException $e) {
            Log::error("'Check' request failed", ['error' => $e->getMessage(), 'response' => isset($checkResponse) ? $checkResponse : null]);

            throw $e;
        } catch (Throwable $e) {
            Log::error("'Check' request failed", ['error' => $e->getMessage(), 'response' => isset($checkResponse) ? $checkResponse : null]);

            throw $e;
        } catch (Exception $e) {
            Log::error("'Check' request failed", ['error' => $e->getMessage(), 'response' => isset($checkResponse) ? $checkResponse : null]);

            throw $e;
        }

        // The response will be invalid if a validation error occured over at Billink.
        if ($checkResponse->isInvalid()) {
            Log::warning("'Check' response appears invalid", compact('checkResponse'));

            return $checkResponse->throwException(FailedCreditCheck::class);
        }

        // This is a CheckResponse specific method. If it is negative, this means the customer
        // cannot order through Billink as it did not pass the credit check.
        if ($checkResponse->isNegative()) {
            throw new FailedCreditCheck($this->getExceptionMessage());
        }

        // At this point the customer did pass the credit check and an UUID is availble
        return $checkResponse->getUuid();
    }
}
