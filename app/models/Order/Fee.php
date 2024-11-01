<?php

namespace Tussendoor\Billink\Order;

use Tussendoor\Billink\Model;

class Fee extends Model
{
    public function getNameAttribute($original)
    {
        return sanitize_text_field($original);
    }

    public function getAmountAttribute($original)
    {
        return floatval($original);
    }

    public function getVatSuffixAttribute($original)
    {
        global $woocommerce;

        if (!function_exists('wc_prices_include_tax')) {
            return $original;
        }

        if (method_exists($woocommerce->cart, 'get_tax_price_display_mode')) {
            $displayMode = $woocommerce->cart->get_tax_price_display_mode();
        } else {
            $displayMode = $woocommerce->cart->tax_display_cart;
        }

        return $displayMode === 'incl' ?
            $woocommerce->countries->inc_tax_or_vat() :
            $woocommerce->countries->ex_tax_or_vat();
    }
}
