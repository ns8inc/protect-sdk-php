<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Installer;

/**
 * Base class for uninstalling the NS8 Protect module
 */
abstract class BaseClient
{
    /**
     * Install the NS8 Protect module.
     *
     * @param string  $platformName The platform we are utilizing (e.g. Magento)
     * @param mixed[] $installData  The data related to the merchant install
     *
     * @return mixed[] The response containing accessToken information
     */
    abstract public static function install(string $platformName, array $installData) : array;
}
