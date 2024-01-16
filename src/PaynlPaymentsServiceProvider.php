<?php

namespace Lobbesnl\Lunar\Paynl;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Lobbesnl\Lunar\Paynl\Facades\PaynlFacade;
use Lunar\Facades\Payments;


class PaynlPaymentsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Register our payment type.
        Payments::extend('paynl', function($app) {
            return $app->make(PaynlPaymentType::class);
        });

        PaynlFacade::initPayInstance();

        Route::group([], function() {
            require __DIR__ . '/routes/web.php';
        });
    }
}