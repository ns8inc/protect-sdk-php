<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Polling\Windows;

use NS8\ProtectSDK\Polling\BaseClient;
use function dirname;
use function exec;
use function sprintf;

/**
 * Manage background service polling logic on Windows
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

        return sprintf('start %s %s/%s', self::getPHPBinaryPath(), $currentDirectory, self::PHP_POLLING_SCRIPT);
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
        exec(sprintf('taskkill /PID %d /F', $processId));
        self::removeProcessIdFile();

        return true;
    }
}
