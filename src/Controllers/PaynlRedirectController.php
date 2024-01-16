<?php

namespace Lobbesnl\Lunar\Paynl\Controllers;

use Lunar\Models\Order;
use Lunar\Models\Transaction;

class PaynlRedirectController
{
    public function redirect(Order $order, Transaction $transaction)
    {
        // TODO: check if transaction has succeeded
        if (!$transaction->reference) {
            return redirect()->route(config('lunar.mollie.payment_failed_route'));
        }

        // Transaction succeeded, authorize payment
        $paymentAuthorize = Payments::driver('paynl')->withData([
            'paymentId' => $transaction->reference,
        ])->authorize();

        if (!$paymentAuthorize->success) {
            $data = json_decode($paymentAuthorize->message, true);

            return match ($data['status']) {
                'open' => redirect()->route(config('lunar.mollie.payment_open_route')),
                'canceled' => redirect()->route(config('lunar.mollie.payment_canceled_route')),
                default => redirect()->route(config('lunar.mollie.payment_failed_route')),
            };
        }

        return redirect()->route(config('lunar.mollie.payment_paid_route'));
    }
}