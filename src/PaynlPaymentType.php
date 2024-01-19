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
use Paynl\Error\Api;
use Paynl\Error\Error;
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
        $address        = $this->order->shippingAddress;
        $invoiceAddress = $this->order->billingAddress;

        $splittedAddress = Helper::splitAddress($address->line_one);
        $payNLAddress    = [
            'streetName'  => $splittedAddress[0],
            'houseNumber' => $splittedAddress[1],
            'zipCode'     => $address->postcode,
            'city'        => $address->city,
            'country'     => $address->country->iso2,
        ];

        $splittedAddress     = Helper::splitAddress($invoiceAddress->line_one);
        $payNLInvoiceAddress = [
            'streetName'  => $splittedAddress[0],
            'houseNumber' => $splittedAddress[1],
            'zipCode'     => $invoiceAddress->postcode,
            'city'        => $invoiceAddress->city,
            'country'     => $invoiceAddress->country->iso2,
        ];

        $amount = $this->order->total;
        $amount = 10;

        $products = [];
        $products = [
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
        ];

        $transactionParameters = [
            # Required
            'amount'         => $amount,
            'returnUrl'      => route(
                $this->data['redirectRoute'],
                ['order' => $this->order->id, 'transaction' => $transaction->id]
            ),

            # Optional
            'currency'       => $this->order->currency->code,
            'exchangeUrl'    => $this->data['webhookUrl'],
            'paymentMethod'  => $this->data['method'],
            'description'    => $this->data['description'],
            'testmode'       => config('lunar.paynl.test_mode') ? 1 : 0,
            'orderNumber'    => $this->order->id,
            'products'       => $products,
            'language'       => app()->getLocale(),
            'ipaddress'      => Helper::getIp(),
            'invoiceDate'    => new DateTime(),
            'deliveryDate'   => new DateTime(),
            'enduser'        => [
                'initials'     => $invoiceAddress->first_name,
                'lastName'     => $invoiceAddress->last_name,
                // 'gender'       => 'M',
                // 'birthDate'    => new DateTime('1990-01-10'),
                'phoneNumber'  => (string) $invoiceAddress->contact_phone,
                'emailAddress' => (string) $invoiceAddress->contact_email,
            ],
            'address'        => $payNLAddress,
            'invoiceAddress' => $payNLInvoiceAddress,
        ];

        if (!empty($this->data['bank'])) {
            $transactionParameters['bank'] = $this->data['bank'];
        }
        if (!empty($this->data['extra1'])) {
            $transactionParameters['extra1'] = $this->data['extra1'];
        }
        if (!empty($this->data['extra2'])) {
            $transactionParameters['extra2'] = $this->data['extra2'];
        }
        if (!empty($this->data['extra3'])) {
            $transactionParameters['extra3'] = $this->data['extra3'];
        }

        $payNLTransaction = \Paynl\Transaction::start($transactionParameters);

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

        $orderId = (int) $transactionData['paymentDetails']['orderNumber'];

        $transaction = Transaction::where('reference', $this->data['paymentId'])
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

        if ($payNLTransaction->isRefunded()) {
            // Handle refund authorization
            $refundTransaction = $this->order->refunds->where('reference', 'R:' . $this->data['paymentId'])->first();

            if (empty($refundTransaction)) {
                $refundTransaction = $this->order->refunds()->create([
                    'success'   => $payNLTransaction->isRefunded(),
                    'type'      => 'refund',
                    'driver'    => 'paynl',
                    'reference' => 'R:' . $this->data['paymentId'],
                    'notes'     => $payNLTransaction->getDescription(),
                    'amount'    => $payNLTransaction->getAmountRefund(),
                    'status'    => $payNLTransaction->getStateName(),
                    'card_type' => '',
                ]);
            }

            $refundTransaction->update([
                'amount'  => $payNLTransaction->getAmountRefund(),
                'success' => ($payNLTransaction->isRefunded()),
                'status'  => $payNLTransaction->getStateName(),
                'meta'    => $transactionData['paymentDetails'],
            ]);

            return new PaymentAuthorize(
                success: $payNLTransaction->isRefunded(),
                message: json_encode(['status' => $payNLTransaction->getStateName()])
            );
        }

        // Handle payment authorization
        if ($this->order->placed_at) {
            return new PaymentAuthorize(
                success: true,
                message: json_encode(['status' => 'duplicate', 'message' => 'This order has already been placed']),
            );
        }

        $transaction->update([
            'success' => ($payNLTransaction->isPaid() || $payNLTransaction->isAuthorized()),
            'status'  => $payNLTransaction->getStateName(),
            'meta'    => $transactionData['paymentDetails'],
        ]);

        if (($payNLTransaction->isPaid() || $payNLTransaction->isAuthorized())) {
            $this->order->placed_at = $transactionData['paymentDetails']['created'];
        }
        $this->order->status = config('lunar.paynl.payment_status_mappings.' . $payNLTransaction->getStateName()) ? : $payNLTransaction->getStateName();
        $this->order->save();

        return new PaymentAuthorize(success: ($payNLTransaction->isPaid() || $payNLTransaction->isAuthorized()),
            message: json_encode(['status' => $payNLTransaction->getStateName()]));
    }



    public function capture(Transaction $transaction, $amount = 0): PaymentCapture
    {
        //Not applicable for Pay.
        return new PaymentCapture(success: true);
    }



    public function refund(Transaction $transaction, int $amount = 0, $notes = null): PaymentRefund
    {
        try {
            $refund = \Paynl\Transaction::refund(
                $transaction->reference,
                $amount,
                $notes ?? 'Refund for order ' . $transaction->order->reference
            );
        } catch (Api|Error $e) {
            return new PaymentRefund(
                success: false,
                message: $e->getMessage()
            );
        }

        $resultData = $refund->getData();

        $refundDetails = [
            'success'   => $resultData['request']['result'] == '1',
            'type'      => 'refund',
            'driver'    => 'paynl',
            'amount'    => $refund->getRefundAmount() / pow(10, $transaction->order->currency->decimal_places),
            'reference' => 'R:' . $transaction->reference,
            'status'    => $resultData['request']['result'],
            'notes'     => $refund->getDescription(),
            'card_type' => '',
        ];

        // Create refund? Only one refund per transaction is allowed
        $refundTransaction = $transaction->order->refunds->where('reference', 'R:' . $transaction->reference)->first();

        if (empty($refundTransaction)) {
            $transaction->order->refunds()->create($refundDetails);
        } else {
            $refundTransaction->update($refundDetails);
        }

        return new PaymentRefund(
            success: true
        );
    }
}