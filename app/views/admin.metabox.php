<?php global $post; ?>
<?php if (empty($order) || ($order === false)) : ?>
    <p><?php echo __('Something went wrong while retrieving the order.', 'woocommerce-gateway-billink'); ?></p>
    <?php return; ?>
<?php endif; ?>
<?php if (empty($orderStatus) || ($orderStatus === false)) : ?>
    <p><?php echo __('Something went wrong while retrieving the order status.', 'woocommerce-gateway-billink'); ?></p>
    <?php return; ?>
<?php endif; ?>

<?php foreach ($orderStatus as $invoice) : ?>
    <div method="POST" action="<?php echo get_edit_post_link($post); ?>" id="billink__actions">
        <ul class="order_actions">
            <li class="wide">
                <p><?php echo __('This order is paid through Billink.nl', 'woocommerce-gateway-billink'); ?></p>

                <label><?php echo __('Execute an action:', 'woocommerce-gateway-billink'); ?></label>
                <select name="billink_action" style="width:100%; margin-bottom: 0.5em;">
                    <option value=""><?php echo __('Select an action', 'woocommerce-gateway-billink'); ?></option>
                    <option value="workflow" <?php echo $invoice->workflowHasStarted() ? 'disabled' : ''; ?>><?php echo __('Start workflow', 'woocommerce-gateway-billink'); ?></option>
                    <option value="credit"><?php echo __('Credit', 'woocommerce-gateway-billink'); ?></option>
                    <option value="message" disabled><?php echo __('Add note [Unavailable]', 'woocommerce-gateway-billink'); ?></option>
                    <option value="onhold" disabled><?php echo __('Put On Hold [Unavailable]', 'woocommerce-gateway-billink'); ?></option>
                </select>

                <div class="credit__fields hidden">
                    <label class="billink_label" for="credit_amount">
                        <?php echo __('Credit amount', 'woocommerce-gateway-billink'); ?>
                        <input id="credit_amount" class="billink_input" type="text" name="billink_credit_amount" value="">
                    </label>

                    <label class="billink_label" for="credit_description">
                        <?php echo __('Description', 'woocommerce-gateway-billink'); ?> <small><?php echo __('(optional, max 254 characters)', 'woocommerce-gateway-billink'); ?></small>
                        <input id="credit_description" class="billink_input" type="text" name="billink_credit_description" value="">
                    </label>
                </div>

                <input type="hidden" name="order_id" value="<?php echo $post->ID; ?>">

                <button class="button billink__submit-action" style="float:right;" form="billink__actions" type="submit"><?php echo __('Execute', 'woocommerce-gateway-billink'); ?></button>
            </li>

            <li class="wide">
                <?php echo __('ID', 'woocommerce-gateway-billink'); ?>: <?php echo $invoice->invoicenumber; ?>, Workflow: <?php echo $order->get_meta('_billink_workflow'); ?><br>
                <?php echo __('Status', 'woocommerce-gateway-billink'); ?>: <?php echo $invoice->status->translated(); ?><br>
                <?php echo __('Description', 'woocommerce-gateway-billink'); ?>: <?php echo $invoice->description; ?><br>
            </li>

            <li class="wide">
                <p>
                    <a href="#" target="_BLANK" class="button" disabled>
                        <span style="line-height: 1.2;" class="dashicons dashicons-external"></span> 
                        <?php echo __('View invoice', 'woocommerce-gateway-billink'); ?>
                    </a>
                </p>
            </li>
        </ul>
    </div>
<?php endforeach; ?>

<div class="billink__loader"></div>