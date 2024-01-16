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
    'test_mode' => env('PAYNL_TEST_MODE', true),

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
];