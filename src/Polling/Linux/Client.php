<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Polling\Linux;

use NS8\ProtectSDK\Polling\BaseClient;
use function dirname;
use function exec;
use function sprintf;

/**
 * Manage background service polling logic on Linux
 */
class Client extends BaseClient
{
    /**
     * Returns the command used to execute the background polling service
     *
     * @return string The command that is needed to begin polling
     */
    protected static function getServiceCommand() : string
    {
        $currentDirectory = dirname(dirname(__FILE__));

        return sprintf('nohup %s %s/%s &', self::getPHPBinaryPath(), $currentDirectory, self::PHP_POLLING_SCRIPT);
    }

    /**
     * Stops the polling background service from running and removes the Process ID File
     *
     * @return bool True if the service was successfully stopped, otherwise false.
     */
    public static function killService() : bool
    {
        if (! self::isServiceRunning()) {
            return false;
        }

        $processId = self::getProcessId();
        exec(sprintf('kill -9 %d', $processId));
        self::removeProcessIdFile();

        return true;
    }
}
