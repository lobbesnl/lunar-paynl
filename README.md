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
php artisan vendor:publish --provider="Lobbesnl\Lunar\Paynl\PaynlPaymentsServiceProvider"
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

