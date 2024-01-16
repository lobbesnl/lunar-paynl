<?php

namespace Lobbesnl\Lunar\Paynl\Controllers;

class PaynlWebhookController
{
    public function webhook(Request $request)
    {
        $paymentId = $request->input('id');

        // TODO: find out what to do with PayNL
        Payments::driver('paynl')->withData([
            'paymentId' => $paymentId,
        ])->authorize();

        return response(null, 200);
    }
}