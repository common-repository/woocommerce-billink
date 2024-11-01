(function($) {
    "use strict";
    
    $(window).on('load', function() {
        // Taken from checkout.js, to ensure we're on the checkout.
        if (typeof wc_checkout_params !== 'undefined') {
            $(document).on('payment_method_selected', triggerCheckoutFormRefresh);
            $(document).on('change', 'input[name=billing_company]', triggerCheckoutFormRefresh);
            $(document).on('change', 'input[name=shipping_company]', triggerCheckoutFormRefresh);
        }
    });
    
    function triggerCheckoutFormRefresh() {
        $(document.body).trigger('update_checkout');
    }
})(jQuery);
