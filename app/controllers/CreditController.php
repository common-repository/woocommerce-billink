<?php

namespace Tussendoor\Billink\Controllers;

use DateTime;
use WC_Order;
use Exception;
use Throwable;
use Tussendoor\Billink\App;
use Tussendoor\Billink\Credit\Credit;
use Tussendoor\Billink\Helpers\Notice;
use Tussendoor\Billink\Credit\CreditList;
use Tussendoor\Billink\Concerns\ExecutesActions;
use Tussendoor\Billink\Exceptions\ValidationError;
use Tussendoor\Billink\Endpoint\Credit as CreditEndpoint;

class CreditController
{
    use ExecutesActions;

    /**
     * Credit an order.
     * @param  WC_Order              $order
     * @param  string|float|int|null $amount
     * @param  string|null           $description
     * @return mixed|null
     */
    public function credit(WC_Order $order, $amount = null, $description = null)
    {
        try {
            list($amount, $description) = $this->sanitizeUserInput($order, $amount, $description);
        } catch (Throwable $e) {
            return $this->trigger('order.credit.failed')->with($e, $order->get_id())->call();
        } catch (Exception $e) {
            return $this->trigger('order.credit.failed')->with($e, $order->get_id())->call();
        }

        $creditEndpoint = $this->getCreditEndpoint($order, $amount, $description);

        try {
            $response = App::get('http.client')
                ->post($creditEndpoint->getUrlEndpoint(), $creditEndpoint->serialize())
                ->send();

            $creditResponse = App::get('serializer')
                ->unserialize($response->getBody(), CreditEndpoint::class, 'xml');
        } catch (Throwable $e) {
            $this->trigger('order.credit.failed')->with($e, $order->get_id())->call();

            return false;
        } catch (Exception $e) {
            $this->trigger('order.credit.failed')->with($e, $order->get_id())->call();

            return false;
        }

        // The response will be invalid if a validation error occured over at Billink.
        if ($creditResponse->isInvalid()) {
            $this->trigger('order.credit.failed')
                ->with($creditResponse->generateException(), $order->get_id())
                ->call();

            return false;
        }

        // Get a list of Credit\Invoice instances, which have methods to figure
        // out the status of an Invoice. The list itself is a Collection.
        $invoices = $creditResponse->getInvoices();
        foreach ($invoices as $invoice) {
            if ($invoice->success() && $this->orderTotalEqualToCreditAmount($order, $amount)) {
                // $order->set_status('cancelled');
                // $order->save();
            }

            return $this->createCreditNotice($invoice);
        }

        // If, for some reason, the foreach did not iterate.
        return false;
    }

    /**
     * Check if the order total is equal to the amount the user wants to credit.
     * @param  \WC_Order $order
     * @param  float     $amount
     * @return bool
     */
    protected function orderTotalEqualToCreditAmount($order, $amount)
    {
        return abs(($order->get_total('edit') - $amount) / $amount) < 0.0000000001;
    }

    /**
     * Create an instance of the CreditEndpoint (Tussendoor\Billink\Endpoint\Credit).
     * @param  \WC_Order                           $order
     * @param  float                               $amount
     * @param  string                              $description
     * @return \Tussendoor\Billink\Endpoint\Credit
     */
    protected function getCreditEndpoint($order, $amount, $description)
    {
        $orderNumber = $order->get_order_number();
        if ($this->orderIsCreatedBeforeRelease($order)) {
            $orderNumber = $order->get_id();
        }

        $credit = new Credit($orderNumber, $amount, $description);
        $creditList = CreditList::collect([$credit]);

        $creditEndpoint = new CreditEndpoint($creditList);

        return $creditEndpoint->setAuthenticationHeader(App::get('auth.header'));
    }

    /**
     * Determine if the order is created before version two of this plugin was released.
     * Before that, orders in Billink were created with the order ID. In the new version,
     * we're using the order number. This is causing issues when crediting version one
     * orders through the version two plugin.
     * @param  \WC_Order $order
     * @return bool
     */
    protected function orderIsCreatedBeforeRelease($order)
    {
        $creationDate = $order->get_date_created();
        if (!is_object($creationDate) || !$creationDate instanceof DateTime) {
            return false;
        }

        return $creationDate <= new DateTime('24-09-2019 14:00');
    }

    /**
     * Validate and sanitize the amount and description, which are input by the user.
     * @param  \WC_Order                                      $order
     * @param  string|float|int|null                          $amount
     * @param  string|null                                    $description
     * @return array
     * @throws \Tussendoor\Billink\Exceptions\ValidationError On validation error
     */
    protected function sanitizeUserInput($order, $amount, $description)
    {
        $amount = (float) str_replace(',', '.', (string) $amount);
        if (empty($amount) || $amount <= 0) {
            throw new ValidationError(__('The amount to credit cannot be zero or empty.', 'woocommerce-gateway-billink'));
        }

        if ($amount > (float) $order->get_total('edit')) {
            throw new ValidationError(__('The amount to credit cannot be greater than the order amount.', 'woocommerce-gateway-billink'));
        }

        if (mb_strlen((string) $description) > 254) {
            throw new ValidationError(__('The credit description cannot contain more than 254 characters.', 'woocommerce-gateway-billink'));
        }

        return [$amount, sanitize_text_field((string) $description)];
    }

    /**
     * Create a notice in the admin area, depending on the success of the workflow start.
     * @param  \Tussendoor\Billink\Credit\Invoice $invoice
     * @return bool
     */
    protected function createCreditNotice($invoice)
    {
        $notice = new Notice();
        $notice->setPriority(9999);

        if ($invoice->notExists()) {
            $notice->failed(__(
                "Unable to credit order: The given order does not exist in Billink.",
                'woocommerce-gateway-billink'
            ))->create();

            return false;
        }

        if ($invoice->notAllowed()) {
            $notice->failed(__(
                "Unable to credit order: The plugin does not have the required permissions.",
                'woocommerce-gateway-billink'
            ))->create();

            return false;
        }

        if ($invoice->notPossible()) {
            $notice->failed(__(
                "Unable to credit order: please contact Billink.",
                'woocommerce-gateway-billink'
            ))->create();

            return false;
        }

        if ($invoice->unkownBankAccount()) {
            $notice->failed(__(
                "Unable to credit order: unknown bankaccount, chargeback could not be completed.",
                'woocommerce-gateway-billink'
            ))->create();

            return false;
        }

        if ($invoice->success()) {
            $notice->successful(sprintf(__("Credit for invoice %d processed.", 'woocommerce-gateway-billink'), $invoice->invoiceNumber))
                ->create();

            return true;
        }

        $notice->failed(__(
            "Unable to determine if the credit was successful, unknown response from Billink.",
            'woocommerce-gateway-billink'
        ))->create();

        return false;
    }
}
