# lunar-paynl
Pay. payment driver for Lunar.

# Installation

### Require the composer package

```sh
composer require lobbesnl/lunar-paynl:dev-main
```

### Publish the configuration

This will publish the configuration under `config/lunar/paynl.php`.

```bash
php artisan vendor:publish --tag=lunar.paynl.config
```

### Enable the driver

Set the driver in `config/lunar/payments.php`

```php
<?php

return [
    // ...
    'types' => [
        'paynl' => [
            'driver' => 'paynl',
        ],
    ],
];
```

### Add your Paynl credentials and other config

Take a look at the configuration in `config/paynl.php`. Where approriate, edit or set the environment variables in your `.env` file. At least the keys will need to be set.

```dotenv
PAYNL_TEST_MODE=true
PAYNL_TOKEN_CODE=
PAYNL_TOKEN_CODE_TEST=
PAYNL_API_TOKEN=
PAYNL_API_TOKEN_TEST=
PAYNL_SERVICE_ID=
PAYNL_SERVICE_ID_TEST=
```

You can use the `PAYNL_TEST_MODE` environment variable to switch between live and test mode.

### create named routes for success and cancellation pages
When the user returns form the payment provider webpage, a redirect will be generated, based on the result of the payment.
Therefore there have to be four named routed, as defined in the config. 

```php
<?php
'payment_paid_route'     => 'checkout-success.view',
'payment_canceled_route' => 'checkout-canceled.view',
'payment_open_route'     => 'checkout-open.view',
'payment_failed_route'   => 'checkout-failure.view',
```

### Example
To start a payment:
```php
<?php
$payment = \Lunar\Facades\Payments::driver('paynl')
    ->cart($this->cart)
    ->withData([
        'description'   => 'Description',
        'redirectRoute' => config('lunar.paynl.redirect_route'),
        'webhookUrl'    => config('lunar.paynl.override_webhook_url') ?: route(config('lunar.paynl.webhook_route')),
        'method'        => $paymentMethod,
        'bank'          => $bankID, // optional
        'extra1'        => '',      // optional
        'extra2'        => '',      // optional
        'extra3'        => '',      // optional
    ])
    ->initiatePayment();

return redirect($payment->getRedirectUrl());
```