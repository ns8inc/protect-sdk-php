<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Merchants;

use stdClass;

/**
 * Base class for working with the NS8 Template Service
 */
abstract class BaseClient
{
    /**
     * The template endpoint.
     */
    const CURRENT_MERCHANT_ENDPOINT = '/merchant/current';

    /**
     * Get the current merchant.
     *
     * @return stdClass The current merchant
     */
    abstract public static function getCurrent() : stdClass;
}
