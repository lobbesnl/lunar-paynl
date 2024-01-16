<?php

namespace Lobbesnl\Lunar\Paynl;

use Illuminate\Support\ServiceProvider;
use Lunar\Facades\Payments;
use Paynl\Config;

class PaynlPaymentsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/mollie.php', 'lunar.mollie');

        // Register our payment type.
        Payments::extend('paynl', function($app) {
            return $app->make(PaynlPaymentType::class);
        });

        if (!config('lunar.paynl.test_mode')) {
            Config::setApiToken(config('lunar.paynl.paynl_api_token'));
            Config::setServiceId(config('lunar.paynl.paynl_service_id'));
            if (!empty(config('lunar.paynl.paynl_tokencode'))) {
                Config::setTokenCode(config('lunar.paynl.paynl_tokencode'));
            }
        } else {
            Config::setApiToken(config('lunar.paynl.paynl_api_token_test'));
            Config::setServiceId(config('lunar.paynl.paynl_service_id_test'));
            if (!empty(config('lunar.paynl.paynl_tokencode_test'))) {
                Config::setTokenCode(config('lunar.paynl.paynl_tokencode_test'));
            }
        }

        Route::group([], function() {
            require __DIR__ . '/../routes/web.php';
        });
    }
}