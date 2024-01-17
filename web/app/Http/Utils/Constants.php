<?php

namespace App\Http\Utils;

abstract class Constants
{
    const DEFAULT_NOTIFY_API_VERSION = "1.3";
    const DEFAULT_KHIPU_CLIENT_TIMEOUT = 30;
    const MAX_INTEGER_LENGTH = 18;
    const MAX_ORDER_EXPIRATION = 31536000;

    const PARAM_SANTANDER_TOKEN_COMPANY = '768300143';
    const PARAM_SANTANDER_TOKEN_USERNAME = '768300143';
    const PARAM_SANTANDER_TOKEN_PASSWORD = 'Ax4o5idb_h';
    const PARAM_SANTANDER_TOKEN_URL = 'https://paymentbutton-bsan-cert.e-pagos.cl';

    const STATUS_CREATED = 'CREATED';
    const STATUS_PREAUTHORIZED = 'PRE-AUTHORIZED';
    const STATUS_AUTHORIZED = 'AUTHORIZED';
    const STATUS_FAILED_OP = 'FAILED_OP';
    const STATUS_FAILED_SIGN = 'FAILED_SIGN';
}
