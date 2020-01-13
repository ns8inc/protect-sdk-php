<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Order;

use stdClass;

/**
 * Base class for the Order client for fetching known, frequently used order attributes.
 */
abstract class BaseClient
{
    /**
     * Get the current merchant.
     *
     * @return stdClass The merchant
     */
    abstract public static function getCurrentMerchant() : stdClass;

    /**
     * Get an order using its name.
     *
     * @param string $name The order name
     *
     * @return stdClass The order
     */
    abstract public static function getOrderByName(string $name) : stdClass;
}
