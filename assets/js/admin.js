(function($) {
    "use strict";
    
    $(window).on('load', function() {
        $(document).on('change', 'select[name=billink_action]', toggleCreditFields);
        $(document).on('input', 'input[name=billink_credit_amount]', validateCreditAmount);
        $(document).on('input', 'input[name=billink_credit_description]', validateCreditDescription);
        $(document).on('click', 'button.billink__submit-action', validateInput);

        $(document).on('keyup', '.billink_input', catchReturn);
    });

    function catchReturn(e) {
        if (e.keyCode == 13) {
            e.preventDefault();
            e.stopPropagation();
            
            return $('.billink__submit-action').trigger('click');
        }
    }

    /**
     * Validate the amount and description fields. Checks if they have the has_error class
     * and if the credit field has any input at all.
     */
    function validateInput(e) {
        $('.billink__loader').removeClass('active');

        if ($('select[name=billink_action]').val() == '') {
            return osxShakeElement($('select[name=billink_action]'));
        }

        if ($('select[name=billink_action]').val() !== 'credit') {
            $('.billink__loader').addClass('active');
            return submitBillinkActionsForm();
        }

        var amountEl = 'input[name=billink_credit_amount]';
        var descriptionEl = 'input[name=billink_credit_description]';
        if (
            $(amountEl).parent('label').hasClass('has_error') ||
            $(descriptionEl).parent('label').hasClass('has_error')
        ) {
            osxShakeElement(e.currentTarget);
            return e.preventDefault();
        }

        if ($(amountEl).val() == '') {
            e.preventDefault();
            osxShakeElement(e.currentTarget);
            return addErrorToField(amountEl, 'Voer een bedrag in.');
        }

        $('.billink__loader').addClass('active');

        return submitBillinkActionsForm();
    }

    function submitBillinkActionsForm() {
        var form = $('div#billink__actions').clone();

        var newForm = $("<form></form>", {
            method: 'POST',
            action: form.attr('action'),
            id: form.attr('id'),
            html: form.contents(),
        });

        $('body').remove('form#billink__actions').append(newForm);

        return $('form#billink__actions').submit();
    }

    /**
     * Toggle displaying the credit fields
     */
    function toggleCreditFields(e) {
        var selected = $(e.currentTarget).val();
        if (selected == 'credit') {
            return displayCreditFields();
        }

        return hideCreditFields();
    }

    /**
     * Validate the input credit amount. It must be a whole number or a decimal.
     */
    function validateCreditAmount(e) {
        var value = $(e.currentTarget).val();
        if (value == '') {
            return removeErrorFromField(e.currentTarget);
        }

        if (value.match(/^\d*(\,\d{0,2})?$/) === null) {
            return addErrorToField(e.currentTarget, 'Het bedrag moet een (komma)getal zijn, met maximaal twee decimalen.');
        }

        value = parseFloat(value.replace(',','.'));
        if (isNaN(value) || value <= 0) {
            return addErrorToField(e.currentTarget, 'Ongeldig bedrag.');
        }

        return removeErrorFromField(e.currentTarget);
    }

    /**
     * Validate the length of the input description. Max 254 characters.
     */
    function validateCreditDescription(e) {
        var value = $(e.currentTarget).val();
        if (value == '' || value.length <= 254) {
            return removeErrorFromField(e.currentTarget);
        }

        return addErrorToField(e.currentTarget, 'De omschrijving mag maximaal 254 tekens bevatten.');
    }

    function displayCreditFields() {
        $('.credit__fields').removeClass('hidden');
    }

    function hideCreditFields() {
        $('.credit__fields').addClass('hidden');
    }

    function addErrorToField(field, error) {
        $(field).parent('label').addClass('has_error').attr('data-error', error);
    }

    function removeErrorFromField(field) {
        $(field).parent('label').removeClass('has_error').attr('data-error', '');
    }

    function osxShakeElement(element) {
        $(element).addClass('osxshake');
        setInterval(function(){ $(element).removeClass('osxshake'); }, 1000);
    }

    // Source: https://github.com/spencertipping/jquery.fix.clone
    // Fixes an issue with clone() where the select value would not be cloned.
    (function (original) {
        jQuery.fn.clone = function () {
            var result           = original.apply(this, arguments),
                my_textareas     = this.find('textarea').add(this.filter('textarea')),
                result_textareas = result.find('textarea').add(result.filter('textarea')),
                my_selects       = this.find('select').add(this.filter('select')),
                result_selects   = result.find('select').add(result.filter('select'));
    
            for (var i = 0, l = my_textareas.length; i < l; ++i) $(result_textareas[i]).val($(my_textareas[i]).val());
            for (var i = 0, l = my_selects.length;   i < l; ++i) {
                for (var j = 0, m = my_selects[i].options.length; j < m; ++j) {
                    if (my_selects[i].options[j].selected === true) {
                        result_selects[i].options[j].selected = true;
                    }
                }
            }
            return result;
        };
    }) (jQuery.fn.clone);

})(jQuery);
