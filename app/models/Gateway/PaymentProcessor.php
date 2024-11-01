<?php

namespace Tussendoor\Billink\Gateway;

use Exception;
use Throwable;
use Tussendoor\Billink\App;
use Tussendoor\Billink\Helpers\Log;
use Tussendoor\Billink\Order\Order;
use Tussendoor\Http\RequestException;
use Tussendoor\Billink\Customer\Customer;
use Tussendoor\Billink\Exceptions\FailedOrderCreation;
use Tussendoor\Billink\Endpoint\Order as OrderEndpoint;

class PaymentProcessor
{
    protected $order;

    public function __invoke(Order $order, Customer $customer)
    {
        return $this->process($order, $customer);
    }

    /**
     * Create a new order in Billink from the given Order and Customer instance.
     * @param  \Tussendoor\Billink\Order\Order                    $order
     * @param  \Tussendoor\Billink\Customer\Customer              $customer
     * @return bool
     * @throws \Tussendoor\Http\RequestException                  When a HTTP error occured
     * @throws \Tussendoor\Billink\Exceptions\FailedOrderCreation When the order could not be created in Billink
     * @throws \Exception                                         When an unknown error occurs
     */
    public function process(Order $order, Customer $customer)
    {
        // Use the Customer and Order instance on the Order endpoint class
        // and inject the AuthenticationHeader into it.
        $orderEndpoint = new OrderEndpoint($customer, $order);
        $orderEndpoint->setAuthenticationHeader(App::get('auth.header'))
            ->setWorkflowNumber($customer->workflowNumber);

        Log::info("Sending 'Order' request", ['request' => $orderEndpoint->serialize()]);

        // Build the HTTP request through our HTTP client and serialize the
        // Endpoint/Order instance into XML by calling the serialize() method.
        try {
            $response = App::get('http.client')
                ->post($orderEndpoint->getUrlEndpoint(), $orderEndpoint->serialize())
                ->send();

            $orderResponse = App::get('serializer')
                ->unserialize($response->getBody(), OrderEndpoint::class, 'xml');
        } catch (RequestException $e) {
            Log::error("'Order' request failed", ['error' => $e->getMessage(), 'response' => isset($orderResponse) ? $orderResponse : null]);

            throw $e;
        } catch (Throwable $e) {
            Log::error("'Order' request failed", ['error' => $e->getMessage(), 'response' => isset($orderResponse) ? $orderResponse : null]);

            throw $e;
        } catch (Exception $e) {
            Log::error("'Order' request failed", ['error' => $e->getMessage(), 'response' => isset($orderResponse) ? $orderResponse : null]);

            throw $e;
        }

        // The response will be invalid if a validation error occured over at Billink.
        if ($orderResponse->isInvalid()) {
            Log::warning("'Order' response appears invalid", compact('orderResponse'));

            return $orderResponse->throwException(FailedOrderCreation::class);
        }

        // This is a OrderResponse specific method. If this method returns false,
        // the order was NOT created by Billink.
        if ($orderResponse->orderWasCreated() === false) {
            Log::warning("Order could not be created in Billink", ['response' => $orderResponse->toArray()]);

            throw new FailedOrderCreation(__('Order creation failed', 'woocommerce-gateway-billink'));
        }

        return true;
    }
}
