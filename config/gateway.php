<?php

namespace Tussendoor\Billink;

return [
    'gateway.id'                => 'billink',
    'gateway.icon'              => apply_filters('billink_icon', App::get('plugin.url') . '/assets/images/logo-svg.svg'),
    'gateway.hasFields'         => true,
    'gateway.methodTitle'       => __('Billink - afterpayment services', 'woocommerce-gateway-billink'),
    'gateway.supports'          => apply_filters('billink_gateway_supports', ['products', 'refunds']),
    'gateway.acceptTermsText'   => apply_filters('billink_accept_terms_text', __('You must be at least 18+ to use this service. If you pay on time, you will avoid additional costs and ensure that you can use Billink\'s services again in the future.', 'woocommerce-gateway-billink')),
];
