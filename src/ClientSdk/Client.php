<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\ClientSdk;

use NS8\ProtectSDK\Config\Manager as SdkConfigManager;

/**
 * Class for dictating general NS8 Analytics components
 */
class Client extends BaseClient
{
    /**
     * Enum values that correspond to `ClientPage` in the Protect Client SDK
     */
    public const CLIENT_PAGE_DASHBOARD         = 'DASHBOARD';
    public const CLIENT_PAGE_ORDER_DETAILS     = 'ORDER_DETAILS';
    public const CLIENT_PAGE_ORDER_RULES       = 'ORDER_RULES';
    public const CLIENT_PAGE_SUSPICIOUS_ORDERS = 'SUSPICIOUS_ORDERS';

    /**
     * Returns Client SDK URL
     *
     * @return string URL to Protect Client SDK
     */
    public static function getClientSdkUrl() : string
    {
        return SdkConfigManager::getEnvValue('urls.js_sdk');
    }
}
