<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\ClientSdk;

/**
 * Base class for interacting with the Protect Client SDK
 */
abstract class BaseClient
{
    /**
     * Returns Client SDK URL
     *
     * @return string URL to Protect Client SDK
     */
    abstract public static function getClientSdkUrl() : string;
}
