<?php

namespace Lobbesnl\Lunar\Paynl\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Lunar\Facades\Payments;

class PaynlWebhookController
{
    public function webhook(Request $request): Application|ResponseFactory|Response
    {
        $paymentId = $request->input('order_id');

        Payments::driver('paynl')
            ->withData(['paymentId' => $paymentId,])
            ->authorize();

        return response(null, 200);
    }
}