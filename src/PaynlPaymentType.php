<?php

namespace Lobbesnl\Lunar\Paynl;

use DateTime;
use Lobbesnl\Lunar\Paynl\Facades\PaynlFacade;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\PaymentTypes\AbstractPayment;
use Lunar\Models\Order;
use Lunar\Models\Transaction;
use Paynl\Helper;
use Paynl\Result\Transaction\Start;


class PaynlPaymentType extends AbstractPayment
{

    public function initiatePayment(): Start
    {
        if (!$this->order) {
            if (!$this->order = $this->cart->draftOrder) {
                $this->order = $this->cart->createOrder();
                $this->cart->load('draftOrder');
            } else {
                $this->order = $this->cart->createOrder(orderIdToUpdate: $this->cart->draftOrder->id);
            }
        }

        if ($this->order->placed_at) {
            throw new \Exception('Order has already been placed');
        }

        $transaction = Transaction::create([
            'success'   => false,
            'driver'    => 'paynl',
            'order_id'  => $this->order->id,
            'type'      => 'capture',
            'amount'    => $this->order->total,
            'reference' => '',
            'status'    => '',
            'card_type' => '',
        ]);

        PaynlFacade::initPayInstance();

        // Create PayNL Transaction
        $address        = $this->cart->addresses()->where(['type' => 'shipping']);
        $invoiceAddress = $this->cart->addresses()->where(['type' => 'billing']);

//        $payNLAddress   = [
//                'streetName'  => $address['line_one'],
//                // 'houseNumber' => '10',
//                'zipCode'     => $address['line_one'],
//                'city'        => $address['line_one'],
//                'country'     => $address['line_one'],
//            ];

        $payNLTransaction = \Paynl\Transaction::start([
            # Required
            'amount'        => 10.00,                                  // TODO: determine amount
            'returnUrl'     => route(
                $this->data['redirectRoute'],
                ['order' => $this->order->id, 'transaction' => $transaction->id]
            ),

            # Optional
            'currency'      => 'EUR',
            'exchangeUrl'   => $this->data['webhookUrl'],
            'paymentMethod' => 10,                                     // TODO: determine payment method
            'bank'          => 1,                                      // TODO: determine bank number
            'description'   => $this->order->id,                       // TODO: determine description
            'testmode'      => config('lunar.paynl.test_mode') ? 1 : 0,
            'extra1'        => 'ext1',
            'extra2'        => 'ext2',
            'extra3'        => $this->order->id,
            'products'      => [
                [
                    'id'    => 1,
                    'name'  => 'een product',
                    'price' => 5.00,
                    'tax'   => 0.87,
                    'qty'   => 1,
                ],
                [
                    'id'    => 2,
                    'name'  => 'ander product',
                    'price' => 5.00,
                    'tax'   => 0.87,
                    'qty'   => 1,
                ],
            ],
            'language'      => 'EN',
            'ipaddress'     => Helper::getIp(),
            'invoiceDate'   => new DateTime(),
            'deliveryDate'  => new DateTime(),
            'enduser'       => [
                // 'initials'     => 'T',
                'lastName'     => 'Test',
                // 'gender'       => 'M',
                // 'birthDate'    => new DateTime('1990-01-10'),
                // 'phoneNumber'  => '0612345678',
                'emailAddress' => 'test@test.nl',
            ],
            'address'       => [
                'streetName'  => 'Test',
                'houseNumber' => '10',
                'zipCode'     => '1234AB',
                'city'        => 'Test',
                'country'     => 'NL',
            ],
            //            'invoiceAddress' => [
            //                'initials'    => 'IT',
            //                'lastName'    => 'ITEST',
            //                'streetName'  => 'Istreet',
            //                'houseNumber' => '70',
            //                'zipCode'     => '5678CD',
            //                'city'        => 'ITest',
            //                'country'     => 'NL',
            //            ],
        ]);

        $transaction->update([
            'reference' => $payNLTransaction->getTransactionId(),
            'status'    => '',
            'notes'     => $payNLTransaction->getPaymentReference(),
        ]);

        return $payNLTransaction;
    }



    public function authorize(): PaymentAuthorize
    {
        if (!array_key_exists('paymentId', $this->data)) {
            return new PaymentAuthorize(
                success: false,
                message: json_encode(['status' => 'not_found', 'message' => 'No payment ID provided']),
            );
        }

        PaynlFacade::initPayInstance();
        $payNLTransaction = \Paynl\Transaction::status($this->data['paymentId']);
        $transactionData  = $payNLTransaction->getData();


        $orderId = $transactionData['extra3'];

        $transaction = Transaction
            ::where('reference', $this->data['paymentId'])
            ->where('order_id', $orderId)
            ->where('driver', 'paynl')
            ->first();

        $this->order = Order::find($orderId);

        if (!$transaction || !$payNLTransaction || !$this->order) {
            return new PaymentAuthorize(
                success: false,
                message: json_encode([
                    'status'  => 'not_found',
                    'message' => 'No transaction found for payment ID ' . $this->data['paymentId'],
                ]),
            );
        }


        // TODO: how to deal with this?
        /*
        foreach ($payment->refunds() as $refund) {
            $transaction = $this->order->refunds->where('reference', $refund->id)->first();
            if ($transaction) {
                $transaction->update([
                    'status' => $refund->status,
                ]);
            }
        }
        */

        if ($this->order->placed_at) {
            return new PaymentAuthorize(
                success: true,
                message: json_encode(['status' => 'duplicate', 'message' => 'This order has already been placed']),
            );
        }

        // TODO: refunds?
        // if (is_null($payment->amountRefunded) || $payment->amountRefunded->value === '0.00') {
        $transaction->update([
            'success' => ($payNLTransaction->isPaid() || $payNLTransaction->isAuthorized()),
            'status'  => $payNLTransaction->getStateName(),
            // 'notes'     => $payment->description,
            //            'card_type' => $payNLTransaction->get$payment->method ?? '',
            //            'meta'      => [
            //                'method'      => $payment->method,
            //                'locale'      => $payment->locale,
            //                'details'     => $payment->details,
            //                'links'       => $payment->_links,
            //                'countryCode' => $payment->countryCode,
            //            ],
        ]);
        //}

        if (($payNLTransaction->isPaid() || $payNLTransaction->isAuthorized())) {
            $this->order->placed_at = $transactionData['stoptime'];
        }
        $this->order->status = config('lunar.mollie.payment_status_mappings.' . $payNLTransaction->getStateName()) ? : $payNLTransaction->getStateName();
        $this->order->save();

        return new PaymentAuthorize(success: ($payNLTransaction->isPaid() || $payNLTransaction->isAuthorized()),
            message: json_encode(['status' => $payNLTransaction->getStateName()]));
    }



    public function capture(Transaction $transaction, $amount = 0): PaymentCapture
    {
    }



    public function refund(Transaction $transaction, int $amount = 0, $notes = null): PaymentRefund
    {
    }
}