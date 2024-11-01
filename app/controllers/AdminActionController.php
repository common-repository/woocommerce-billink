<?php

namespace Tussendoor\Billink\Controllers;

use Tussendoor\Billink\Helpers\Notice;
use Tussendoor\Billink\Concerns\ExecutesActions;

class AdminActionController
{
    use ExecutesActions;

    /**
     * If the 'billink_action' key is set, we'll parse the request for actions.
     */
    public function register()
    {
        if (isset($_POST['billink_action']) && !empty($_POST['billink_action'])) {
            add_action('init', [$this, 'parseAction']);
        }
    }

    /**
     * Figure out if the current request contains data which indicates if the user
     * wants to perform an action on an order.
     * @return mixed|null
     */
    public function parseAction()
    {
        list($action, $order) = $this->parseRequest();
        if (empty($action) || empty($order)) {
            return (new Notice())->failed(__(
                "Billink action cannot be executed: invalid action and/or order.",
                'woocommerce-gateway-billink'
            ))->create();
        }

        switch ($action) {
            case 'workflow':
                return $this->trigger('order.workflow.start')->with($order)->call();
            case 'credit':
                // Data sanitisation is performed in the CreditController.
                return $this->trigger('order.credit')->with(
                    $order,
                    isset($_POST['billink_credit_amount']) ? $_POST['billink_credit_amount'] : null,
                    isset($_POST['billink_credit_description']) ? $_POST['billink_credit_description'] : null
                )->call();
            case 'fullcredit':
                // @todo implement
                return false;
        }
    }

    /**
     * Parse the request for an action and an order ID. Kills the request if the current
     * user is not logged in or does not have the 'manage_woocommerce' capability.
     * @return array Use list($action, $order)
     */
    protected function parseRequest()
    {
        if (!is_user_logged_in() || !current_user_can('manage_woocommerce')) {
            wp_die(__('You are not allowed to edit Billink orders.', 'woocommerce-gateway-billink'));
        }

        $action = sanitize_text_field($_POST['billink_action']);
        $order = wc_get_order(isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0);

        return [$action, $order];
    }
}
