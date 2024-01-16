<?php

namespace Lobbesnl\Lunar\Paynl\Facades;

use Illuminate\Support\Facades\Facade;
use Paynl\Config;
use Paynl\Paymentmethods;

class PaynlFacade extends Facade
{
    public static function initPayInstance(): void
    {
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
    }



    public static function getPaymentMethods(array $options = [], string $languageCode = null): array
    {
        self::initPayInstance();

        return Paymentmethods::getList($options, $languageCode);
    }
}