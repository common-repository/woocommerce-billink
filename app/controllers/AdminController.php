<?php

namespace Tussendoor\Billink\Controllers;

use WC_Order;
use Exception;
use Throwable;
use Tussendoor\Billink\App;
use Tussendoor\Billink\Helpers\Log;
use Tussendoor\Http\RequestException;
use Tussendoor\Billink\Order\OrderList;
use Tussendoor\Billink\Endpoint\Status as StatusEndpoint;
use Tussendoor\Billink\Converters\Order as OrderConverter;

class AdminController
{
    public function register()
    {
        add_action('add_meta_boxes', [$this, 'loadMetaBox'], 10, 2);

        add_action('admin_enqueue_scripts', [$this, 'registerStylesheet']);
        add_action('admin_enqueue_scripts', [$this, 'registerJavascriptFiles']);
    }

    public function registerJavascriptFiles()
    {
        wp_register_script('billink_script', App::get('plugin.url') . '/assets/js/admin.js', ['jquery'], App::get('plugin.version'), true);
    }

    public function registerStylesheet()
    {
        wp_register_style('billink-style', App::get('plugin.url') . '/assets/css/admin.css', [], App::get('plugin.version'));
    }

    public function loadMetaBox($post_type, $post)
    {
        if ($post_type != 'shop_order') return;

        $order = wc_get_order($post->ID);
        if ($order->get_meta('_billink_workflow', true, 'edit') != 1) return;

        try {
            $orderStatus = $this->getOrderStatus($order);
        } catch (Throwable $e) {
            Log::info("Unable to resolve order status", ['order' => $order->get_id(), $e->getMessage()]);
            $orderStatus = false;
        } catch (Exception $e) {
            Log::info("Unable to resolve order status", ['order' => $order->get_id(), $e->getMessage()]);
            $orderStatus = false;
        }

        wp_enqueue_script('billink_script');
        wp_enqueue_style('billink-style');

        add_meta_box('billink-order', 'Billink', function ($post) use ($order, $orderStatus) {
            include App::get('plugin.viewpath') . '/admin.metabox.php';
        }, $post_type, 'side', 'high');
    }

    /**
     * Get the order status in Billink.
     * @param  \WC_Order                                      orderId
     * @return \Tussendoor\Billink\Status\Invoice
     * @throws \Tussendoor\Http\RequestException              When a HTTP error occurs
     * @throws \Tussendoor\Billink\Exceptions\InvalidResponse If a validation error occured over at Billink
     * @throws \Exception                                     An unknown error occured
     */
    protected function getOrderStatus($order)
    {
        if (!is_object($order) || !$order instanceof WC_Order) {
            throw new Exception("Unknown/invalid order ID: no order found.");
        }

        $order = (new OrderConverter($order))->parse();
        $orderList = new OrderList([$order]);

        $statusEndpoint = new StatusEndpoint($orderList);
        $statusEndpoint->setAuthenticationHeader(App::get('auth.header'));

        try {
            $response = App::get('http.client')
                ->post($statusEndpoint->getUrlEndpoint(), $statusEndpoint->serialize())
                ->send();
            Log::debug('Sending STATUS request', ['request' => $statusEndpoint->serialize()]);

            if ($response->getBody() == '') {
                throw new RequestException("Empty response from Billink");
            }

            $statusResponse = App::get('serializer')
                ->unserialize($response->getBody(), StatusEndpoint::class, 'xml');
        } catch (Throwable $e) {
            Log::error("'Status' request failed", ['error' => $e->getMessage(), 'response' => isset($statusResponse) ? $statusResponse : null]);

            throw $e;
        } catch (Exception $e) {
            Log::error("'Status' request failed", ['error' => $e->getMessage(), 'response' => isset($statusResponse) ? $statusResponse : null]);

            throw $e;
        }

        // The response will be invalid if a validation error occured over at Billink.
        if ($statusResponse->isInvalid()) {
            Log::warning("'Order' response appears invalid", compact('statusResponse'));

            return $statusResponse->throwException();
        }

        return $statusResponse->getInvoices();
    }
}
