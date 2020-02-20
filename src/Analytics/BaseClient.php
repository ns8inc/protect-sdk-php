<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Analytics;

/**
 * Base class for dictating general NS8 Analytics methods
 */
abstract class BaseClient
{
    /**
     * Returns TrueStats URL
     *
     * @return bool Returns true if NS8 Access Token is valid, false otherwise
     */
    abstract public static function getTrueStatsRoute() : string;

    /**
     * Returns the TrueStats JavaScript block
     *
     * @return string A string containing TrueStats JavaScript to load on front-end pages
     */
    abstract public static function getTrueStatsScript() : string;
}
