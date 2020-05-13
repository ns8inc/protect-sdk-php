<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Polling\Windows;

use NS8\ProtectSDK\Polling\BaseClient;
use function count;
use function dirname;
use function exec;
use function explode;
use function preg_match;
use function shell_exec;
use function sprintf;
use function strlen;
use function substr;

/**
 * Manage background service polling logic on Windows
 */
class Client extends BaseClient
{
    /**
     * Regex to match PHP app for executing the script
     */
    public const PHP_REGEX = '/php[^\\\]*\.exe$/i';

    public const PHP_APP_EXTENSION = '.exe';

    /**
     * Returns the command used to execute the background polling service
     *
     * @return string The command that is needed to begin polling
     */
    protected static function getServiceCommand() : string
    {
        $currentDirectory       = dirname(dirname(__FILE__));
        $phpBinary              = '';
        $phpBinaryOptionsString = shell_exec('where php');
        $phpBinaryOptionsArray  = empty($phpBinaryOptionsString) ? [] : explode('\n', (string) $phpBinaryOptionsString);
        $defaultphpBinary       = self::getPHPBinaryPath();
        if (preg_match(self::PHP_REGEX, $defaultphpBinary)) {
            $phpBinary = $defaultphpBinary;
        } elseif (count($phpBinaryOptionsArray) && preg_match(self::PHP_REGEX, (string) $phpBinaryOptionsArray[0])) {
            $phpBinary = phpBinaryOptionsArray[0];
        }

        $command = '';
        if (! empty($phpBinary)) {
            // Parse out ".exe" ending to enable passing file argument in
            $phpBinary = substr($phpBinary, 0, strlen(self::PHP_APP_EXTENSION)*-1);
            $command   = sprintf('start %s %s\\%s', $phpBinary, $currentDirectory, self::PHP_POLLING_SCRIPT);
        }

        return $command;
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
