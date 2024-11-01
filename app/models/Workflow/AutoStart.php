<?php

namespace Tussendoor\Billink\Workflow;

use WC_Order;
use Tussendoor\Billink\App;
use Tussendoor\Billink\Gateway\Settings;
use Tussendoor\Billink\Concerns\ExecutesActions;

class AutoStart
{
    use ExecutesActions;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
        $this->status = (string) $settings->autoworkflow_status;
    }

    public function getStatus()
    {
        return str_replace('wc-', '', $this->status);
    }

    public function getAction()
    {
        return 'woocommerce_order_status_' . $this->getStatus();
    }

    public function isEnabled()
    {
        $status = $this->getStatus();

        return ! empty($status) && $status !== 'disabled';
    }

    public function setup()
    {
        return add_action($this->getAction(), [$this, 'triggerWorkflowStart']);
    }

    public function triggerWorkflowStart($order)
    {
        $order = wc_get_order($order);

        if (empty($order) || !$this->checkPaymentMethod($order)) {
            return false;
        }

        return $this->trigger('order.workflow.start')->with($order)->call();
    }

    public function checkPaymentMethod($order)
    {
        return $order->get_payment_method() == App::get('gateway.id');
    }
}
