<tr valign="top">
    <th scope="row" class="titledesc">
        <label for="<?php echo esc_attr($fieldKey); ?>"><?php echo wp_kses_post($data['title']); ?> <?php echo $tooltip; ?></label>
    </th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text"><span>
                <?php echo wp_kses_post($data['title']); ?>
            </span></legend>
            <?php $i = 0; ?>
            <div class="workflow__config-col">
                <?php foreach ($selected as $workflowConfig) : ?>
                    <div class="workflow__config" data-row="<?php echo $i; ?>">
                        <label><?php echo __('Country', 'woocommerce-gateway-billink'); ?></label>
                        <div style="display: inline-block; float: right;">
                            <button class="button workflow__config-delete">x</button>
                        </div>
                        <br>
                        <select name="<?php printf('%s[%d][%s]', esc_attr($fieldKey), $i, 'country'); ?>">
                            <?php foreach ($countries as $code => $label) : ?>
                                <option value="<?php echo $code; ?>" <?php echo $code == $workflowConfig['country'] ? 'selected="selected"' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <br>
                        <label for="isBusiness">
                            <?php echo __('Business', 'woocommerce-gateway-billink'); ?>
                            <input type="checkbox" name="<?php printf('%s[%d][%s]', esc_attr($fieldKey), $i, 'isBusiness'); ?>" value="1" <?php echo $workflowConfig['isBusiness'] == '1' ? 'checked="checked"' : ''; ?>>
                        </label>
                        <br>
                        <label><?php echo __('Workflow number', 'woocommerce-gateway-billink'); ?></label>
                        <br>
                        <input type="text" name="<?php printf('%s[%d][%s]', esc_attr($fieldKey), $i, 'workflow_number'); ?>" value="<?php echo $workflowConfig['workflow_number']; ?>">
                        <hr>
                    </div>
                    <?php $i++; ?>
                <?php endforeach; ?>
            </div>

            <p><button class="button workflow__config-create">
                <?php echo __('New configuration', 'woocommerce-gateway-billink'); ?>
            </button></p>

            <div class="workflow__config-blueprint hidden">
                <label><?php echo __('Country', 'woocommerce-gateway-billink'); ?></label>
                <div style="display: inline-block; float: right;">
                    <button class="button workflow__config-delete">x</button>
                </div>
                <br>
                <select name="<?php printf('%s[%s][%s]', esc_attr($fieldKey), '%ID%', 'country'); ?>">
                    <?php foreach ($countries as $code => $label) : ?>
                        <option value="<?php echo $code; ?>">
                            <?php echo $label; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br>
                <label for="isBusiness">
                    <?php echo __('Business', 'woocommerce-gateway-billink'); ?>
                    <input type="checkbox" name="<?php printf('%s[%s][%s]', esc_attr($fieldKey), '%ID%', 'isBusiness'); ?>" value="1">
                </label>
                <br>
                <label><?php echo __('Workflow number', 'woocommerce-gateway-billink'); ?></label>
                <br>
                <input type="text" name="<?php printf('%s[%s][%s]', esc_attr($fieldKey), '%ID%', 'workflow_number'); ?>" value="">
                <hr>
            </div>
            
            <?php echo $description; ?>
        </fieldset>
    </td>
</tr>

<style type="text/css">
    .workflow__config {
        display: inline-block;
        box-sizing: border-box;
        padding: 1em;
        border: 1px dashed #c4c4c4;
        margin: 0.5em;
    }
</style>

<script type="text/javascript">
    (function($) {
        "use strict";
        
        $(window).load(function() {
            initializeCountryDropdown();
            $(document).on('click', '.workflow__config-create', createNewWorkflowConfig);
            $(document).on('click', '.workflow__config-delete', removeWorkflowConfig);
        });

        function createNewWorkflowConfig(e) {
            e.preventDefault();

            var rowId = getWorkflowRowId();
            var blueprint = $('.workflow__config-blueprint').clone();
            blueprint.removeClass('workflow__config-blueprint hidden')
                .addClass('workflow__config')
                .attr('data-row', rowId);

            blueprint.find('select')
                .attr('name', blueprint.find('select').attr('name').replace('%ID%', rowId));
            blueprint.find('input[type=checkbox]')
                .attr('name', blueprint.find('input[type=checkbox]').attr('name').replace('%ID%', rowId));
            blueprint.find('input[type=text]')
                .attr('name', blueprint.find('input[type=text]').attr('name').replace('%ID%', rowId));

            $('.workflow__config-col').append(blueprint);
        }

        function removeWorkflowConfig(e) {
            e.preventDefault();

            $(e.currentTarget).parents('.workflow__config').remove();
        }

        function getWorkflowRowId() {
            var rowIds = $('.workflow__config').toArray().map(function(element) {
                return $(element).attr('data-row');
            }).sort();
            
            return parseInt(rowIds[rowIds.length - 1]) + 1;
        }

        function initializeCountryDropdown() {
            $('.country__select:visible').selectWoo();
        }
        
    })(jQuery);

</script>
