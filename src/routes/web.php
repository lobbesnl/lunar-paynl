<?php

use Lobbesnl\Lunar\Paynl\Controllers\PaynlRedirectController;
use Lobbesnl\Lunar\Paynl\Controllers\PaynlWebhookController;

Route::middleware('web')->group(function() {
    Route::get('paynl/redirect/{order}/{transaction}', [PaynlRedirectController::class, 'redirect'])
        ->name('paynl.redirect');
});

Route::match(['GET', 'POST'], 'paynl/webhook', [PaynlWebhookController::class, 'webhook'])
    ->name('paynl.webhook');
