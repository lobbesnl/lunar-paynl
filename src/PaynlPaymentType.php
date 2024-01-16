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
    }



    public function capture(): PaymentCapture
    {
    }



    public function refund(): PaymentRefund
    {
    }
}