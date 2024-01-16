<?php

namespace Lobbesnl\Lunar\Paynl;

use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\PaymentTypes\AbstractPayment;

class PaynlPaymentType extends AbstractPayment
{
    public function authorize(): PaymentAuthorize
    {
        if (!array_key_exists('paymentId', $this->data)) {
            return new PaymentAuthorize(
                success: false,
                message: json_encode(['status' => 'not_found', 'message' => 'No payment ID provided']),
            );
        }
    }



    public function capture(): PaymentCapture
    {
    }



    public function refund(): PaymentRefund
    {
    }
}