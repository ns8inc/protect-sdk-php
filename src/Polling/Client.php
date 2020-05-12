<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Polling;

use const PHP_OS;
use function call_user_func_array;
use function strtoupper;
use function substr;

/**
 * Client for invoking Polling logic
 */
class Client
{
    /**
     * Define set of Operating Systems to support
     */
    public const OS_LINUX   = 'LINUX';
    public const OS_WINDOWS = 'WINDOWS';

    // Define OS Client paths as ::CLASS is not available until PHP 5.5
    public const LINUX_CLIENT   = 'NS8\ProtectSDK\Polling\Linux\Client';
    public const WINDOWS_CLIENT = 'NS8\ProtectSDK\Polling\Windows\Client';

    /**
     * Map operating systems to their respective clients
     */
    public const CLASS_MAPPING = [
        self::OS_LINUX => self::LINUX_CLIENT,
        self::OS_WINDOWS => self::WINDOWS_CLIENT,
    ];

    /**
     * The type of Operating System being utilized
     *
     * @var string $osType
     */
    protected static $osType = null;

    /**
     * Call method for given operating system client
     * @param string $method The method for the static class being called
     * @param mixed[] $args An array of arguments to supply the given method
     *
     * @return mixed Return static function result
     */
    public static function __callStatic($method, $args)
    {
        $operatingSystem = self::getOperatingSystem();

        return call_user_func_array(self::CLASS_MAPPING[$operatingSystem] . '::' . $method, $args);
    }

    /**
     * Fetch the operating system. If we cannot determine it then assume Linux
     *
     * @return string The operating system constant
     */
    protected static function getOperatingSystem() : string
    {
        if (! empty(self::$osType)) {
            return self::$osType;
        }
        self::$osType = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? self::OS_WINDOWS : self::OS_LINUX;

        return self::$osType;
    }
}
