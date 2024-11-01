<?php

namespace Tussendoor\Billink\Controllers;

use WC_Order;
use Exception;
use Throwable;
use Tussendoor\Billink\App;
use Tussendoor\Billink\Helpers\Log;
use Tussendoor\Billink\Helpers\Notice;
use Tussendoor\Billink\Order\OrderList;
use Tussendoor\Billink\Workflow\AutoStart;
use Tussendoor\Billink\Concerns\ExecutesActions;
use Tussendoor\Billink\Converters\Order as OrderConverter;
use Tussendoor\Billink\Endpoint\StartWorkflow as StartWorkflowEndpoint;

class WorkflowController
{
    use ExecutesActions;

    public function register()
    {
        add_action('init', [$this, 'autoStartWorkflow']);
    }

    /**
     * Automatically start workflows, if enabled.
     * @return bool
     */
    public function autoStartWorkflow()
    {
        $settings = App::get('billink.settings')();
        $autoStart = new AutoStart($settings);

        return $autoStart->isEnabled() ? $autoStart->setup() : false;
    }

    /**
     * Start the workflow of an Order.
     * @param  WC_Order $order
     * @return bool
     */
    public function start(WC_Order $order)
    {
        $orderList = OrderList::collect([(new OrderConverter($order))->parse()]);

        $workflowEndpoint = new StartWorkflowEndpoint($orderList);
        $workflowEndpoint->setAuthenticationHeader(App::get('auth.header'));
        Log::debug(
            'Sending START WORKFLOW request',
            ['request' => $workflowEndpoint->serialize()]
        );

        try {
            $response = App::get('http.client')
                ->post($workflowEndpoint->getUrlEndpoint(), $workflowEndpoint->serialize())
                ->send();

            $workflowResponse = App::get('serializer')
                ->unserialize($response->getBody(), StartWorkflowEndpoint::class, 'xml');
        } catch (Throwable $e) {
            $this->trigger('order.startworkflow.failed')->with($e, $order->get_id())->call();

            return false;
        } catch (Exception $e) {
            $this->trigger('order.startworkflow.failed')->with($e, $order->get_id())->call();

            return false;
        }

        // The response will be invalid if a validation error occured over at Billink.
        if ($workflowResponse->isInvalid()) {
            $this->trigger('order.startworkflow.failed')
                ->with($workflowResponse->generateException(), $order->get_id())
                ->call();

            return false;
        }

        // Get a list of Workflow\Invoice instances, which have methods to figure
        // out the status of an Invoice. The list itself is a Collection.
        $invoices = $workflowResponse->getInvoices();
        foreach ($invoices as $invoice) {
            return $this->createWorkflowNotice($invoice);
        }

        return true; //?
    }

    /**
     * Create a notice in the admin area, depending on the success of the workflow start.
     * @param  \Tussendoor\Billink\Workflow\Invoice $invoice
     * @return bool                                 Indicates the success of the notice created.
     */
    protected function createWorkflowNotice($invoice)
    {
        $notice = new Notice();

        if ($invoice->notExists()) {
            $notice->failed(__(
                "Unable to start workflow: The given order does not exist in Billink.",
                'woocommerce-gateway-billink'
            ))->create();

            return false;
        }

        if ($invoice->alreadyStarted()) {
            $notice->failed(__(
                "Unable to start workflow: the workflow of the given order has already started.",
                'woocommerce-gateway-billink'
            ))->create();

            return false;
        }

        if ($invoice->success()) {
            $notice->successful(sprintf(
                __("Workflow for invoice %d started.", 'woocommerce-gateway-billink'),
                $invoice->invoiceNumber
            ))->create();

            return true;
        }

        $notice->failed(__(
            "Unable to determine if workflow started, unknown response from Billink.",
            'woocommerce-gateway-billink'
        ))->create();

        return false;
    }
}
