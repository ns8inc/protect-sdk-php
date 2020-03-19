<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Uninstaller;

use stdClass;

/**
 * Base class for uninstalling the NS8 Protect module
 */
abstract class BaseClient
{
    /**
     * Uninstall the NS8 Protect module.
     *
     * @return stdClass The response from the uninstallation request
     */
    abstract public static function uninstall() : stdClass;
}
