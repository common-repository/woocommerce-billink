<tr valign="top">
    <th scope="row" class="titledesc">
        <label for="<?php echo esc_attr($fieldKey); ?>"><?php echo wp_kses_post($data['title']); ?> <?php echo $tooltip; ?></label>
    </th>
    <td class="forminp">
        <?php echo $description; ?>
        <fieldset>
            <legend class="screen-reader-text"><span>
                <?php echo wp_kses_post($data['title']); ?>
            </span></legend>
            <div class="paymentcosts__instructions">
                <h3><?php echo __('Payment Costs instructions', 'woocommerce-gateway-billink'); ?></h3>
                <p><?php echo __('Configure additional costs passed to the customer. Select a country to restrict the costs to the country of the customer or select "Any country" to disable the restriction.', 'woocommerce-gateway-billink'); ?></p>
                <p><?php echo __('Select a minimum and maximum order amount. When the order amount is between those two numbers, the costs will be applied. Leave the maximum order amount empty to put no restriction on the order amount. Decimals are not supported. The order amount used to calculate the fee is excluding any other fee.', 'woocommerce-gateway-billink'); ?></p>
                <p><?php echo __('Next, select a costs type. "Percentage" calculates a percentage based cost based off of the order amount. Set the type to "Fixed" to apply a fixed costs to the order.', 'woocommerce-gateway-billink'); ?></p>
                <p><?php echo __('The last input either contains the percentage of costs or the fixed costs.', 'woocommerce-gateway-billink'); ?></p>
                <br>
                <p><?php echo __("Please note: payment costs are applied in the order they're added. If some costs overlap, only the first encountered costs will be applied.", 'woocommerce-gateway-billink'); ?></p>
            </div>
            <?php $i = 0; ?>
            <table class="costs__table">
                <thead>
                    <tr>
                        <th><?php echo __('Country', 'woocommerce-gateway-billink'); ?></th>
                        <th><?php echo __('Orderamount', 'woocommerce-gateway-billink'); ?></th>
                        <th><?php echo __('Type', 'woocommerce-gateway-billink'); ?></th>
                        <th><?php echo __('Percentage', 'woocommerce-gateway-billink'); ?>/Cost</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($selected as $costsConfig) : ?>
                        <tr data-row="<?php echo $i; ?>" class="payment__costs">
                            <td>
                                <select name="<?php printf('%s[%d][%s][]', esc_attr($fieldKey), $i, 'country'); ?>" class="country__select" multiple="multiple">
                                    <?php foreach ($countries as $code => $label) : ?>
                                        <option value="<?php echo $code; ?>" <?php echo in_array($code, $costsConfig['country']) ? 'selected="selected"' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <input type="number" name="<?php printf('%s[%d][%s]', esc_attr($fieldKey), $i, 'min'); ?>" value="<?php echo $costsConfig['min']; ?>" placeholder="min order amount">
                                <input type="number" name="<?php printf('%s[%d][%s]', esc_attr($fieldKey), $i, 'max'); ?>" value="<?php echo $costsConfig['max']; ?>" placeholder="max order amount">
                            </td>
                            <td>
                                <select name="<?php printf('%s[%d][%s]', esc_attr($fieldKey), $i, 'type'); ?>">
                                    <option value="percentage" <?php echo $costsConfig['type'] == 'percentage' ? 'selected="selected"' : ''; ?>>Percentage</option>
                                    <option value="fixed" <?php echo $costsConfig['type'] == 'fixed' ? 'selected="selected"' : ''; ?>>Fixed amount</option>
                                </select>
                            </td>
                            <td>
                                <input type="string" name="<?php printf('%s[%d][%s]', esc_attr($fieldKey), $i, 'value'); ?>" value="<?php echo $costsConfig['value']; ?>" min="0" step=".01">
                            </td>
                            <td>
                                <button class="payment__costs-delete button">&times;</button>
                            </td>
                        </tr>
                        <?php $i++; ?>
                    <?php endforeach; ?>
                    <tr data-row="%ID%" class="payment__costs-blueprint hidden">
                        <td>
                            <select name="<?php printf('%s[%s][%s][]', esc_attr($fieldKey), '%ID%', 'country'); ?>" class="country__select" multiple="multiple">
                                <?php foreach ($countries as $code => $label) : ?>
                                    <option value="<?php echo $code; ?>">
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input type="number" name="<?php printf('%s[%s][%s]', esc_attr($fieldKey), '%ID%', 'min'); ?>" value="" placeholder="min order amount">
                            <input type="number" name="<?php printf('%s[%s][%s]', esc_attr($fieldKey), '%ID%', 'max'); ?>" value="" placeholder="max order amount">
                        </td>
                        <td>
                            <select name="<?php printf('%s[%s][%s]', esc_attr($fieldKey), '%ID%', 'type'); ?>">
                                <option value="percentage"><?php echo __('Percentage', 'woocommerce-gateway-billink'); ?></option>
                                <option value="fixed"><?php echo __('Fixed amount', 'woocommerce-gateway-billink'); ?></option>
                            </select>
                        </td>
                        <td>
                            <input type="number" name="<?php printf('%s[%s][%s]', esc_attr($fieldKey), '%ID%', 'value'); ?>" min="0" step=".01">
                        </td>
                        <td>
                            <button class="payment__costs-delete button">&times;</button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <p><button class="button payment__costs-create">
                <?php echo __('New configuration', 'woocommerce-gateway-billink'); ?>
            </button></p>
        </fieldset>
    </td>
</tr>

<style type="text/css">
    .costs__table { border: 1px solid #e5e5e5; margin-bottom: 2em; }
    .costs__table thead { background: #ccc; }
    .costs__table thead th { text-align: center; }
    .costs__table tbody tr:nth-child(odd) { background: #e5e5e5; }
    .costs__table select, .costs__table input { width: 100% !important; }
    .paymentcosts__instructions { margin: 20px 0; }
</style>

<script type="text/javascript">
    (function($) {
        "use strict";
        
        $(window).load(function() {
            $(document).on('click', '.payment__costs-create', createNewPaymentCosts);
            $(document).on('click', '.payment__costs-delete', removePaymentCostsRow);
        });

        function removePaymentCostsRow(e) {
            e.preventDefault();

            $(e.currentTarget).parents('.payment__costs').remove();

            return orderPaymentCostsTable();
        }

        function orderPaymentCostsTable() {
            var count = 0;
            var rows = $('.costs__table tbody tr').toArray().reverse();
            var magicRegex = /([\w]+)(\[\d+\])(.+)/;
            if (rows.length <= 0) {
                return;
            }

            for (var i = rows.length - 1; i >= 0; i--) {
                if ($(rows[i]).attr('data-row') === '%ID%') {
                    continue;
                }

                $(rows[i]).attr('data-row', count);

                $(rows[i]).find('select, input').attr('name', $(rows[i]).find('select, input').attr('name').replace(magicRegex, '$1['+count+']$3'));

                count++;
            }
        }

        function createNewPaymentCosts(e) {
            e.preventDefault();

            var rowId = getPaymentCostsRowId();
            var blueprint = $('.payment__costs-blueprint').clone();
            blueprint.removeClass('payment__costs-blueprint hidden')
                .addClass('payment__costs')
                .attr('data-row', rowId);

            var inputs = blueprint.find('select, input');
            for (var i = inputs.length - 1; i >= 0; i--) {
                if (typeof $(inputs[i]).attr('name') === "undefined") {
                    continue;
                }
                $(inputs[i]).attr('name', $(inputs[i]).attr('name').replace('%ID%', rowId));
            }

            $('.costs__table tbody').append(blueprint);

            initializeCountryDropdown();
        }

        function getPaymentCostsRowId() {
            return $('.costs__table tbody tr').length - 1;
        }

        function initializeCountryDropdown() {
            $('.country__select:visible').selectWoo();
        }
        
    })(jQuery);
</script>
