<?php

namespace Tussendoor\Billink\Gateway;

use Exception;
use Throwable;
use WC_Payment_Gateway;
use Tussendoor\Billink\App;
use Tussendoor\Billink\Helpers\Log;
use Tussendoor\Billink\Order\Currency;
use Tussendoor\Billink\Exceptions\UserError;
use Tussendoor\Billink\Helpers\ParameterBag;
use Tussendoor\Billink\Concerns\ExecutesActions;
use Tussendoor\Billink\Exceptions\InternalError;

class Gateway extends WC_Payment_Gateway
{
    use ExecutesActions;

    protected $gatewaySettings;
    protected $canBeEnabled;

    public function __construct()
    {
        $this->initialise();

        // Inherited methods from WC_Payment_Gateway
        $this->init_form_fields();
        $this->init_settings();
    }

    /**
     * Handle the payment of an order. Utilizes multiple processors to create the order
     * in the Billink platform.
     * @param  int        $orderId
     * @return array|void
     */
    public function process_payment($orderId)
    {
        Log::info('Processing payment', compact('orderId'));

        try {
            // Process the orderId into our Order and Customer models.
            $wcOrder = wc_get_order($orderId);
            list($wcOrder, $order, $customer) = (new OrderProcessor())->process($wcOrder, $this->request);

            // Process the gateway settings and the customer model to retrieve the correct workflow number.
            $workflowNumber = (new WorkflowNumberProcessor($this->gatewaySettings))->process($customer);
            $customer->workflowNumber = $workflowNumber;

            // Perform a check on the Customer/Order. If the customer failed the check, a
            // FailedCreditCheck exceptions is thrown with the given exception message.
            $checkProcessor = new CheckProcessor();
            $checkUuid = $checkProcessor->setExceptionMessage($this->gatewaySettings->error_denied)
                ->process($order, $customer);

            $customer->checkUuid = $checkUuid;

            // By now the customer has passed the credit check, so we'll create the order.
            (new PaymentProcessor())->process($order, $customer);
        } catch (UserError $e) {
            // If an UserError occured, we'll rollback the fee that's appended to the order
            // and display the message in the UserException to the user.
            $this->trigger('order.processing.failed')->with($e, $orderId)->call();

            return wc_add_notice($e->getMessage(), 'error');
        } catch (InternalError $e) {
            // The same basically applies to an InternalError, but we will not display the
            // message in the exception itself, but we'll use a generic error message.
            $this->trigger('order.processing.failed')->with($e, $orderId)->call();

            return wc_add_notice(__('An error occured. Please contact the shop owner.', 'woocommerce-gateway-billink'), 'error');
        } catch (Throwable $e) {
            // If a 'normal' Exception occured, it's quite likely the error is a technical one.
            // We'll log it with a high loglevel and return a generic error message.
            $this->trigger('order.processing.failed')->with($e, $orderId)->call();

            return wc_add_notice(__('An unkown error occured. Please contact the shop owner.', 'woocommerce-gateway-billink'), 'error');
        } catch (Exception $e) {
            // If a 'normal' Exception occured, it's quite likely the error is a technical one.
            // We'll log it with a high loglevel and return a generic error message.
            $this->trigger('order.processing.failed')->with($e, $orderId)->call();

            return wc_add_notice(__('An unkown error occured. Please contact the shop owner.', 'woocommerce-gateway-billink'), 'error');
        }

        Log::info('Payment processed, order created.', ['order' => $order->toArray(), 'customer' => $customer->toArray()]);

        // Add a filter to change the order status to a different status than the default 'processing'.
        add_filter('woocommerce_payment_complete_order_status', [$this, 'setPaymentCompleteStatus']);
        $wcOrder->payment_complete();
        remove_filter('woocommerce_payment_complete_order_status', [$this, 'setPaymentCompleteStatus']);

        // Add some meta data, so we can display a metabox on the back-end.
        $wcOrder->update_meta_data('_ordered_through_billink', 1);
        $wcOrder->update_meta_data('_billink_workflow', $customer->workflowNumber);
        $wcOrder->save();

        // Empty the user' cart, as the order is now 'completed'.
        \WC()->cart->empty_cart();

        return ['result' => 'success', 'redirect' => $this->get_return_url($wcOrder)];
    }

    public function setPaymentCompleteStatus()
    {
        return $this->gatewaySettings->order_status ? $this->gatewaySettings->order_status : 'processing';
    }

    /**
     * Handle the refund of an order.
     * @param  int           $orderId
     * @param  float|null    $amount
     * @param  string        $reason
     * @return bool|WP_Error
     */
    public function process_refund($orderId, $amount = null, $reason = '')
    {
        $order = wc_get_order($orderId);
        if (empty($order)) {
            return false;
        }

        if (mb_strlen($reason) > 254) {
            $reason = mb_substr($reason, 0, 250) . '...';
        }

        $credit = $this->trigger('order.credit')->with($order, $amount, $amount)->call();
        if ($credit === false) {
            return new \WP_Error(1, __('The order could not be credited in Billink.'));
        }

        return true;
    }

    /**
     * Make the Settings class publicly accesible.
     * @return Settings
     */
    public function settings()
    {
        return $this->gatewaySettings;
    }

    // @codingStandardsIgnoreStart
    public function init_form_fields()
    {
        $this->form_fields = apply_filters('billink_gateway_fields', App::get('gateway.fields'));
    }

    /**
     * Payment fields before actual payment
     * @return mixed
     */
    public function payment_fields()
    {
        // Render the description. Replaces some variables in the description.
        echo new DescriptionRenderer($this->get_description(), $this->getFee());

        $fields = App::get('gateway.extra.fields');

        $fields['billink_accept']['label'] = sprintf(
            __('I accept the %sBillink terms%s', 'woocommerce-gateway-billink'),
            '<a href="'.$this->gatewaySettings->terms_link.'" target="_BLANK">',
            '</a>'
        );

        $fieldValidator = new PaymentFieldValidator($fields);

        // Render the additional fields.
        $fieldRenderer = new FieldRenderer(apply_filters(
            'billink_extra_gateway_fields',
            $fieldValidator->validate()
        ), $this->request);

        echo $fieldRenderer->render();
    }

    public function validate_fields()
    {
        try {
            (new FieldValidationProcessor())->process($this->request);
        } catch (Throwable $e) {
            wc_add_notice($e->getMessage(), 'error');

            return false;
        } catch (Exception $e) {
            wc_add_notice($e->getMessage(), 'error');

            return false;
        }

        return true;
    }

    public function is_available()
    {
        $available = $this->enabled === 'yes' && (bool) $this->canBeEnabled;

        return apply_filters('billink_is_available', $available, $this);
    }
    // @codingStandardsIgnoreEnd

    /**
     * Setup the base properties inherited from WC_Payment_Gateway
     */
    protected function initialise()
    {
        $this->gatewaySettings = new Settings($this->plugin_id, App::get('gateway.id'));

        $this->request = $this->getRequestData();

        $this->id = App::get('gateway.id');
        $this->icon = App::get('gateway.icon');
        $this->has_fields = App::get('gateway.hasFields');
        $this->method_title = App::get('gateway.methodTitle');
        $this->supports = App::get('gateway.supports');
        $this->canBeEnabled = $this->canBeEnabled();
        $this->title = $this->gatewaySettings->title;
        $this->description = $this->gatewaySettings->description;

        add_action(
            'woocommerce_update_options_payment_gateways_' . $this->id,
            [$this, 'process_admin_options']
        );
    }

    /**
     * Resolve a Fee instance that contains the additional fee for usage of this gateway.
     * @return Tussendoor\Billink\Order\Fee
     */
    protected function getFee()
    {
        global $woocommerce;

        $itemValue = wc_prices_include_tax() ?
            $woocommerce->cart->get_cart_contents_total() :
            $woocommerce->cart->get_cart_contents_total() + $woocommerce->cart->get_cart_contents_tax();

        $shippingValue = wc_prices_include_tax() ?
            $woocommerce->cart->get_shipping_total() :
            $woocommerce->cart->get_shipping_total() + $woocommerce->cart->get_shipping_tax();

        $orderTotal = $itemValue + $shippingValue;

        $feeProcessor = new FeeProcessor($this->gatewaySettings->additional_cost);
        $fee = $feeProcessor->process($orderTotal);

        return $fee;
    }

    /**
     * Check if the gateway can be enabled. Checks if the order amount is not too high and
     * if the country and currency of the order are supported.
     * @return bool
     */
    protected function canBeEnabled()
    {
        return $this->orderHasAcceptableTotal()
            && $this->currencyIsSupported()
            && $this->countryIsSupported();
    }

    /**
     * Validate if the order (or cart) is not above the maximum order amount.
     * @return bool
     */
    protected function orderHasAcceptableTotal()
    {
        global $woocommerce;
        $minAmount = $this->gatewaySettings->min_order_amount;
        $maxAmount = $this->gatewaySettings->max_order_amount;

        // Apparently, this gets triggered while no WC_Cart instance is bound
        // to the WooCommerce object. Return early if that's the case.
        if (!$woocommerce->cart) {
            return false;
        }

        // If the order amount is smaller than the minimum amount, abort.
        if (!empty($minAmount) && round($woocommerce->cart->total, 2) < floatval($minAmount)) {
            return false;
        }

        // If the order amount is bigger than the maximum amount, abort.
        if (!empty($maxAmount) && round($woocommerce->cart->total, 2) > floatval($maxAmount)) {
            return false;
        }

        return true;
    }

    /**
     * Check if the currency of the order is supported.
     * @return bool
     */
    protected function currencyIsSupported()
    {
        return in_array(
            get_woocommerce_currency(),
            apply_filters('billink_supported_currencies', Currency::getValues())
        );
    }

    /**
     * Check if the country of the billing country is supported.
     * @return bool
     */
    protected function countryIsSupported()
    {
        $enabledCountries = $this->gatewaySettings->country_enabled;
        if (empty($enabledCountries)) {
            return true;
        }

        global $woocommerce;

        return $woocommerce->customer ?
            in_array($woocommerce->customer->get_billing_country(), $enabledCountries) :
            false;
    }

    /**
     * Put the complete request into a simple to use ParameterBag.
     * @return \Tussendoor\Billink\Helpers\ParameterBag
     */
    protected function getRequestData()
    {
        $bag = new ParameterBag($_REQUEST);

        // If the current request contains post_data (usually from the checkout),
        // parse the data and merge it into the ParameterBag.
        if ($bag->has('post_data')) {
            if (is_array($bag->get('post_data'))) {
                $postdata = $bag->get('post_data');
            } else {
                parse_str((string) $bag->get('post_data'), $postdata);
            }
            $bag->add($postdata);
        }

        // If this is not the 'order-pay' page, then we're done here.
        if (is_wc_endpoint_url('order-pay') === false) {
            return apply_filters('billink_request_data', $bag, $this);
        }

        // But if it is, we'll see if we can resolve the order that's being paid.
        $orderId = absint(get_query_var('order-pay'));
        $order = wc_get_order($orderId);
        if (empty($order)) {
            return $bag;
        }

        // If there's a valid order, we'll get some of the data from it and put it
        // into our ParameterBag. This way, it'll look like this is the 'normal'
        // checkout.
        $orderData = $order->get_data();
        foreach ($orderData['billing'] as $key => $value) {
            $bag->add([$key => $value]);
            $bag->add(['billing_' . $key => $value]);
        }

        foreach ($orderData['shipping'] as $key => $value) {
            $bag->add(['s_' . $key => $value]);
            $bag->add(['shipping_' . $key => $value]);
        }

        return apply_filters('billink_request_data', $bag, $this);
    }
    // @codingStandardsIgnoreStart
    protected function generate_billink_workflow_html($key, $data)
    {
        return (new Settings\Workflow($this, $this->gatewaySettings))->render($key, $data);
    }

    protected function generate_billink_costs_html($key, $data)
    {
        return (new Settings\PaymentCost($this, $this->gatewaySettings))->render($key, $data);
    }

    protected function validate_billink_workflow_field($key, $value)
    {
        return (new Settings\Workflow($this, $this->gatewaySettings))->validate($key, $value);
    }

    protected function validate_billink_costs_field($key, $value)
    {
        return (new Settings\PaymentCost($this, $this->gatewaySettings))->validate($key, $value);
    }
    // @codingStandardsIgnoreEnd
}
