<?php

namespace Tussendoor\Billink\Controllers;

use WC_Cart;
use ReflectionClass;
use Tussendoor\Billink\App;
use Tussendoor\Billink\Order\Fee;
use Tussendoor\Billink\Gateway\Gateway;
use Tussendoor\Billink\Gateway\Settings;
use Tussendoor\Billink\Gateway\FeeProcessor;
use Tussendoor\Billink\Gateway\CartFeeProcessor;

class GatewayController
{
    const GATEWAY_COMPANY_SESSION_KEY = 'billink_billing_company_session_key';

    /**
     * Register the required actions and filter for this controller
     */
    public function register()
    {
        add_filter('woocommerce_payment_gateways', [$this, 'loadGateway']);
        add_action('wp_enqueue_scripts', [$this, 'loadScript']);
        add_action('woocommerce_cart_calculate_fees', [$this, 'processCartFee'], 99);
        add_filter('woocommerce_available_payment_gateways', [$this, 'disableBillink']);
        add_action('woocommerce_thankyou_billink', [$this, 'displayThankyouMessage'], 10, 0);
        add_action('woocommerce_checkout_update_order_review', [$this,'addCustomBillinkCompanySessionKey']);

        add_action('wp_head', function () {
            ?>
            <style type="text/css">
                .wc_payment_method.payment_method_billink img {
                    display: inline-block;
                    height: 100%;
                    max-height: 25px !important;
                    float: right;
                }
                .wc_payment_method.payment_method_billink p {
                    padding-bottom: 10px;
                }
            </style>
            <?php
        });
    }

    /**
     * Add or remove the Billink fee to or from the cart. It's included in the controller,
     * because the gateway is loaded too late.
     * 
     * todo - Check if the fees get processed towards Billink correctly again
     * 
     * @param  \WC_Cart $cart
     * @return bool
     */
    public function processCartFee(WC_Cart $cart)
    {
        $cartTotal = $this->getCartTotal($cart);

        $config = $this->getPaymentCostsConfig();

        $feeProcessor = new FeeProcessor($config);
        $fee = $feeProcessor->process($cartTotal);

        $feeProcessor = new CartFeeProcessor($fee);
        $feeProcessor->rollback($cart);

        if ($this->cartShouldHaveBillinkCosts()) {
            return $feeProcessor->process($cart);
        }

        return true;
    }

    /**
     * Check if the cart should have Billink costs. True when the Billink
     * gateway is selected, when outside the Netherlands or when the customer
     * is a business in the Netherlands.
     * 
     * @return bool
     */
    private function cartShouldHaveBillinkCosts()
    {
        $paymentMethod = \WC()->session->get('chosen_payment_method');
        $ourGateWayIsSelected = ($paymentMethod === App::get('gateway.id'));

        // Not our Gateway is no costs
        if ($ourGateWayIsSelected === false) {
            return false;
        }

        $defaultCountry = \WC()->countries->get_base_country();
        $customer = \WC()->session->get('customer');
        $country = ($customer['country'] ?? $defaultCountry);

        // Outside the Netherlands is always costs
        $outsideTheNetherlands = ($country !== 'NL');
        if ($outsideTheNetherlands) {
            return true;
        }

        // Inside the Netherlands is only costs for companies
        $isBusinessToBusiness = $this->checkoutSessionIsForB2B();
        if ($isBusinessToBusiness === true) {
            return true;
        }

        return false;
    }

    /**
     * Check if the checkout session is for a B2B customer. We prioritize the
     * custom session key over the default session key to be sure we have the
     * correct company name. The default sometimes stays empty.
     * @return bool
     */
    private function checkoutSessionIsForB2B()
    {
        $companyNameViaCustomSession = \WC()->session->get(self::GATEWAY_COMPANY_SESSION_KEY, 'keydoesnotexist');
        $customerViaDefaultSession = \WC()->session->get('customer');

        $companyName = ($customerViaDefaultSession['company'] ?? '');

        if ($companyNameViaCustomSession !== 'keydoesnotexist') {
            $companyName = $companyNameViaCustomSession;
        }

        $isB2B = (strlen($companyName) > 0);

        return $isB2B;
    }

    /**
     * Because the company name is not always available in the default session 
     * key, we'll store it in a custom session key. This way we can be sure we 
     * have the correct company name when we need it. We also store empty 
     * company names.
     * 
     * @param  string $stringifiedPostData
     * @return void
     */
    public function addCustomBillinkCompanySessionKey(string $stringifiedPostData)
    {
        parse_str($stringifiedPostData, $postData);
        if (isset($postData['billing_company'])) {
            \WC()->session->set(self::GATEWAY_COMPANY_SESSION_KEY, $postData['billing_company']);
        }
    }

    /**
     * Add our gateway to the array used by WooCommerce.
     * @param  array $gateways
     * @return array
     */
    public function loadGateway($gateways)
    {
        $gateways[] = Gateway::class;

        return $gateways;
    }

    /**
     * Register and enqueue some javascript, needed for the gateway.
     */
    public function loadScript()
    {
        wp_register_script(
            'billink_gateway_script',
            App::get('plugin.url') . '/assets/js/front.js',
            ['jquery'],
            App::get('plugin.version'),
            true
        );

        wp_enqueue_script('billink_gateway_script');
    }

    /**
     * Disable the Billink gateway when the user is on the 'order-pay' page and
     * should be paying a fee to use Billink. This is because we cannot calculate
     * additional gateway fees when using this page.
     * @see https://github.com/woocommerce/woocommerce/issues/17794 Issue reference
     * @param  array $gateways
     * @return array
     */
    public function disableBillink($gateways)
    {
        if (isset($gateways['billink']) && is_wc_endpoint_url('order-pay')) {
            $order = wc_get_order(absint(get_query_var('order-pay')));

            if (is_a($order, 'WC_Order')) {
                $orderTotal = $order->calculate_totals();

                $config = $this->getPaymentCostsConfig();

                $feeProcessor = new FeeProcessor($config);
                $fee = $feeProcessor->process($orderTotal);

                if ($fee->amount > 0) {
                    unset($gateways['billink']);
                }
            }
        }

        return $gateways;
    }

    /**
     * Whenever an order is paid through Billink, a custom message can be displayed.
     * @param \WC_Order $order
     */
    public function displayThankyouMessage()
    {
        $ref = new ReflectionClass(Gateway::class);
        $props = $ref->getDefaultProperties();

        $settings = new Settings(
            isset($props['plugin_id']) ? $props['plugin_id'] : 'woocommerce_',
            App::get('gateway.id')
        );

        $message = $settings->thankyou_message;

        echo $message ? wpautop(wptexturize(wp_kses_post($message))) : '';
    }

    /**
     * Get the total of the given cart.
     * @param  \WC_Cart $cart
     * @return float
     */
    protected function getCartTotal($cart)
    {
        // before 2.1.9 bug reference #526056
        // $itemValue = wc_prices_include_tax() ?
        //     $cart->get_cart_contents_total() :
        //     $cart->get_cart_contents_total() + $cart->get_cart_contents_tax();

        // $shippingValue = wc_prices_include_tax() ?
        //     $cart->get_shipping_total() :
        //     $cart->get_shipping_total() + $cart->get_shipping_tax();

        // Since 2.1.9
        $itemValue = $cart->get_cart_contents_total() + $cart->get_cart_contents_tax();
        $shippingValue = $cart->get_shipping_total() + $cart->get_shipping_tax();

        return $itemValue + $shippingValue;
    }

    /**
     * Try and resolve the payment costs from the options table
     * @return []
     */
    protected function getPaymentCostsConfig()
    {
        $settings = get_option('woocommerce_billink_settings');

        return isset($settings['additional_cost']) ? $settings['additional_cost'] : [];
    }
}
