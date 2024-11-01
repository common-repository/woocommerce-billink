<?php

return [
    'gateway.fields'    => [
        /**
         * Basic and authentication settings
         */
        'enabled'   => [
            'title'         => __('Enable/Disable', 'woocommerce-gateway-billink'),
            'type'          => 'checkbox',
            'label'         => __('Enable Billink', 'woocommerce-gateway-billink'),
            'default'       => 'yes'
        ],
        'country_enabled'   => [
            'title'         => __('Enable Billink for specific countries', 'woocommerce-gateway-billink'),
            'type'          => 'multiselect',
            'label'         => __('Enable Billink for specific countries', 'woocommerce-gateway-billink'),
            'description'   => __('Enable the Billink gateway for specific countries only. Replaces the old "Hide for non-dutch customers" option. Leave empty to enable Billink for all countries. Limits within Billink do still apply.', 'woocommerce-gateway-billink'),
            'default'       => [],
            'class'         => 'country__select',
            'custom_attributes' => ['multiple' => 'multiple'],
            'options'       => (new \WC_Countries())->get_countries(),
            'desc_tip'      => true,
        ],
        'title'     => [
            'title'         => __('Title', 'woocommerce-gateway-billink'),
            'type'          => 'text',
            'description'   => __('The name of this payment method which will be shown to the customer when making the payment.', 'woocommerce-gateway-billink'),
            'default'       => __('Billink', 'woocommerce-gateway-billink'),
            'desc_tip'      => true,
        ],
        'description' => [
            'title'         => __('Description', 'woocommerce-gateway-billink'),
            'type'          => 'textarea',
            'description'   => __('The description which will be shown when the custoner selects Billink as payment method.', 'woocommerce-gateway-billink'),
            'default'       => __("Easily pay afterward with Billink.", 'woocommerce-gateway-billink'),
            'desc_tip'      => true,
        ],
        'user' => [
            'title'         => __('Username', 'woocommerce-gateway-billink'),
            'type'          => 'text',
            'description'   => __('Your username at Billink', 'woocommerce-gateway-billink'),
            'default'       => '',
            'desc_tip'      => true,
            'placeholder'   => 'username'
        ],
        'userid' => [
            'title'         => __('Billink ID', 'woocommerce-gateway-billink'),
            'type'          => 'text',
            'description'   => __('Your user ID at Billink.', 'woocommerce-gateway-billink'),
            'default'       => '',
            'desc_tip'      => true,
            'placeholder'   => 'your-billink-id'
        ],
        'extended_workflow' => [
            'title'         => __('Workflow Extended', 'woocommerce-gateway-billink'),
            'type'          => 'billink_workflow',
            'description'   => __('The workflow number used by Billink. You can specify a workflow per country and if the customer is a business or not. Contact Billink if you\'re unsure what number you should use.', 'woocommerce-gateway-billink'),
            'default'       => '',
            'desc_tip'      => true,
        ],
        'workflow' => [
            'title'         => __('Fallback Workflow', 'woocommerce-gateway-billink'),
            'type'          => 'text',
            'description'   => __('If a customer does not match the above workflow rules, this fallback workflow will be used.', 'woocommerce-gateway-billink'),
            'default'       => '1',
            'desc_tip'      => true,
        ],

        'general_settings' => [
            'title'         => __('Settings', 'woocommerce-gateway-billink'),
            'type'          => 'title',
        ],
        'additional_cost' => [
            'title'         => __('Payment costs', 'woocommerce-gateway-billink'),
            'type'          => 'billink_costs',
            'description'   => __('Costs which will be passed to the customer when paying through Billink.', 'woocommerce-gateway-billink'),
            'default'       => '0',
        ],
        'min_order_amount' => [
            'title'         => __('Minum order amount', 'woocommerce-gateway-billink'),
            'type'          => 'number',
            'description'   => __('The minimum amount that can be settled with Billink. If this field is kept empty or set to 0, the plugin allow any order amount. Limits within Billink do still apply. Use a dot (.) as a decimal separator', 'woocommerce-gateway-billink'),
            'default'       => '0',
        ],
        'max_order_amount' => [
            'title'         => __('Maximum order amount', 'woocommerce-gateway-billink'),
            'type'          => 'number',
            'description'   => __('The maximum amount that can be settled with Billink. If this field is kept empty or set to 0, the plugin allow any order amount. Limits within Billink do still apply. Use a dot (.) as a decimal separator', 'woocommerce-gateway-billink'),
            'default'       => '0',
        ],

        'order_status'  => [
            'title'         => __('WooCommerce order status', 'woocommerce-gateway-billink'),
            'type'          => 'select',
            'description'   => __('Change the WooCommerce order status after a customer has succesfully ordered through Billink. The default is \'processing\'.', 'woocommerce-gateway-billink'),
            'default'       => 'wc-processing',
            'desc_tip'      => true,
            'options'       => wc_get_order_statuses(),
        ],
        'autoworkflow_status'  => [
            'title'         => __('Start workflow automatically', 'woocommerce-gateway-billink'),
            'type'          => 'select',
            'description'   => __('Select the WooCommerce order status after which the workflow in Billink should be started.', 'woocommerce-gateway-billink'),
            'default'       => 'disabled',
            'desc_tip'      => true,
            'options'       => array_merge(
                ['disabled' => __('Disabled', 'woocommerce-gateway-billink')],
                wc_get_order_statuses())
        ],
        'error_denied' => [
            'title'         => __('Notification when rejected', 'woocommerce-gateway-billink'),
            'type'          => 'text',
            'description'   => __('Message which will be shown when Billink doens\'t accept an customer.', 'woocommerce-gateway-billink'),
            'default'       => __('Sorry, Billink has rejected your payment request.', 'woocommerce-gateway-billink')
        ],
        'thankyou_message' => [
            'title'         => __('Message when succesfully processed.', 'woocommerce-gateway-billink'),
            'type'          => 'textarea',
            'description'   => __('Message which will be shown on the thank you page of placing an order.', 'woocommerce-gateway-billink'),
            'default'       => __(' ', 'woocommerce-gateway-billink')
        ],


        'terms_link' => [
            'title'         => __('Billink terms', 'woocommerce-gateway-billink'),
            'type'          => 'text',
            'description'   => __('Add the URL to the Billink terms. The default terms of Billink are found here: https://www.billink.nl/voorwaarden/gebruikersvoorwaarden.pdf', 'woocommerce-gateway-billink'),
            'default'       => 'https://www.billink.nl/voorwaarden/gebruikersvoorwaarden.pdf?v=39694984',
        ],

        'testing' => [
            'title'         => __('Advanced Settings', 'woocommerce-gateway-billink'),
            'type'          => 'title',
            'description'   => '',
        ],
        'testmode' => [
            'title'         => __('Billink testing', 'woocommerce-gateway-billink'),
            'type'          => 'checkbox',
            'label'         => __('Enable test mode. If enabled, orders are sent to test.billink.nl instead of app.billink.nl. Enable the "BILLINK_DEBUG" constant for additional logging.', 'woocommerce-gateway-billink'),
            'default'       => 'no'
        ],
        'debug' => [
            'title'         => __('Logging', 'woocommerce-gateway-billink'),
            'type'          => 'checkbox',
            'label'         => __('Since version 2.0.0, logging is enabled by default.', 'woocommerce-gateway-billink'),
            'default'       => 'yes',
            'disabled'      => true,
            'description'   => nl2br(sprintf(
                __("The logs are since version 2.0.0 handled by the logging system in WooCommerce. \nThey can be found by navigating to the menu item 'WooCommerce' -> 'Status' -> 'Logs'. \nOr click this link to view the page directly: %s \n\n Don't forget to change the log to one of Billink, by selecting it from the dropdown in the upper right corner.", 'woocommerce-gateway-billink'),
                sprintf('<a href="%1$s">%1$s</a>', admin_url('admin.php?page=wc-status&tab=logs'))
            )),
        ],
    ],
    'gateway.extra.fields' => [
        'billink_birthdate'     => [
            'type'              => 'text',
            'label'             => __('Birthdate', 'woocommerce-gateway-billink'),
            'placeholder'       => 'dd-mm-jjjj',
            'required'          => true,
            'forBusiness'       => false,
            'custom_attributes' => ['style' => 'width: 200px;'],
            'rules'             => [
                'required'          => __('Birthdate is required.', 'woocommerce-gateway-billink'),
                'match'             => [
                    'pattern' => '~^(?:(?:0?[1-9])|(?:[1-2]\d)|(?:3[0-1]))-(?:(?:0?[1-9])|(?:1[0-2]))-[1-2]\d{3}$~',
                    'error' => __('Birthdate must me formatted as dd-mm-yyyy.', 'woocommerce-gateway-billink'),
                ],
            ],
            'priority'          => 100,
        ],
        'billink_chamber_of_commerce' => [
            'type'              => 'text',
            'label'             => __('Chamber of Commerce number', 'woocommerce-gateway-billink'),
            'placeholder'       => '12345678',
            'required'          => true,
            'forBusiness'       => true,
            'custom_attributes' => ['style' => 'width: 200px;'],
            'rules'             => [
                'required'          => __('Chamber of Commerce number is required.', 'woocommerce-gateway-billink'),
            ],
            'priority'          => 100,
        ],
        'billink_vat_number' => [
            'type'              => 'text',
            'label'             => __('VAT number', 'woocommerce-gateway-billink'),
            'placeholder'       => 'NL1234578B01',
            'required'          => false,
            'forBusiness'       => true,
            'custom_attributes' => ['style' => 'width: 200px;'],
            'priority'          => 100,
        ],
        'billing_phone'     => [
            'label'             => __('Phonenumber', 'woocommerce-gateway-billink'),
            'required'          => true,
            'type'              => 'tel',
            'validate'          => ['phone'],
            'rules'             => [
                'required'          => __('A phonenumber is required.', 'woocommerce-gateway-billink'),
            ],
            'autocomplete'      => 'tel',
            'custom_attributes' => ['style' => 'width: 200px;'],
            'priority'          => 200,
        ],
        'billink_accept'    => [
            'type'              => 'checkbox',
            'label'             => 'I accept the Billink terms',
            'required'          => true,
            'rules'             => [
                'required'          => __('You must accept the Billink terms.', 'woocommerce-gateway-billink'),
            ],
            'priority'          => 999,
        ],
    ],
];
