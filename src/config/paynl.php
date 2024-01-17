<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Pay.nl Test mode
    |--------------------------------------------------------------------------
    |
    | Use Pay.nl sandbox. Defaults to true
    |
    */
    'test_mode'             => env('PAYNL_TEST_MODE', true),

    /*
    |--------------------------------------------------------------------------
    | Pay.nl Token Code
    |--------------------------------------------------------------------------
    |
    | The Pay.nl token code for your website. You can find it in your
    | Pay.nl dashboard. Starts with AT-
    |
    */
    'paynl_tokencode'       => env('PAYNL_TOKEN_CODE'),
    'paynl_tokencode_test'  => env('PAYNL_TOKEN_CODE_TEST'),

    /*
    |--------------------------------------------------------------------------
    | Pay.nl API Token
    |--------------------------------------------------------------------------
    |
    | The Pay.nl API token for your website. You can find it in your
    | Pay.nl dashboard.
    |
    */
    'paynl_api_token'       => env('PAYNL_API_TOKEN'),
    'paynl_api_token_test'  => env('PAYNL_API_TOKEN_TEST'),

    /*
    |--------------------------------------------------------------------------
    | Pay.nl Service ID
    |--------------------------------------------------------------------------
    |
    | The Pay.nl Service ID for your website. You can find it in your
    | Pay.nl dashboard. Starts with SL-
    |
    */
    'paynl_service_id'      => env('PAYNL_SERVICE_ID'),
    'paynl_service_id_test' => env('PAYNL_SERVICE_ID_TEST'),


    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | These are the routes names that will be used to redirect the customer to after
    | the payment has been completed. The default redirect_route and webhook_route
    | are included in the packages routes file, so you don't have to create them
    | yourself. If you want to use your own routes, you can change them here.
    |
    | The redirect_route will be called when the user is redirected back to your
    | website from the Mollie payment screen. Depending on the outcome of the
    | payment attempt, the user will again be redirected to one of the four
    | payment status routes. These routes being part of your theme, they
    | aren't included in the package, be sure to create them yourself.
    */
    'redirect_route'        => 'paynl.redirect',
    'webhook_route'         => 'paynl.webhook',
    'override_webhook_url'  => env('PAYNL_WEBHOOK_URL', null),


    /*
    |--------------------------------------------------------------------------
    | Payment status mappings
    |--------------------------------------------------------------------------
    |
    | The payment statuses you receive from Pay.nl will be mapped to the statuses
    | of your orders using the mapping below. Ideally, the values on the right
    | hand side should also be present in your lunar/orders.php config file.
    */

    'payment_status_mappings' => [
        'open'     => 'payment-open',
        'canceled' => 'payment-canceled',
        'pending'  => 'payment-pending',
        'expired'  => 'payment-expired',
        'failed'   => 'payment-failed',
        'paid'     => 'payment-received',
    ],
];