<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Polling;

use Throwable;
use const PHP_BINARY;
use function dirname;
use function file_exists;
use function file_get_contents;
use function json_decode;
use function posix_kill;
use function proc_open;
use function sprintf;
use function strtotime;
use function time;
use function unlink;

/**
 * Manage background service polling logic
 */
class Client extends BaseClient
{
    /**
     * Attribute to store process details once fetched
     *
     * @var mixed[] $processDetails
     */
    protected static $processDetails = [];

    /**
     * Retrieve and store process details from Process Id File Path
     *
     * @return mixed[] Details regarding the process
     */
    protected static function getProcessDetails() : array
    {
        if (! self::isServiceRunning() || ! empty(self::$processDetails)) {
            return self::$processDetails;
        }
        $processDetailsString = file_get_contents(self::getProcessIdFilePath());
        self::$processDetails = json_decode($processDetailsString, true);

        return self::$processDetails;
    }

    /**
     * Return the process ID of the background polling process
     *
     * @return int|null The process ID as an integer
     */
    protected static function getProcessId() : ?int
    {
        $processDetails = self::getProcessDetails();

        return isset($processDetails['process_id']) ? (int) $processDetails['process_id'] : null;
    }

    /**
     * Returns the path for the PHP binary executable on the current system
     *
     * @return string The path for the PHP executable
     */
    protected static function getPHPBinaryPath() : string
    {
        return PHP_BINARY;
    }

    /**
     * Removes the file storing the process ID
     *
     * @return void
     */
    protected static function removeProcessIdFile() : void
    {
        unlink(self::getProcessIdFilePath());
    }

    /**
     * Returns the command used to execute the background polling service
     *
     * @return string The command that is needed to begin polling
     */
    protected static function getServiceCommand() : string
    {
        $currentDirectory = dirname(__FILE__);

        return sprintf('/usr/bin/nohup php %s/%s &', $currentDirectory, self::PHP_POLLING_SCRIPT);
    }

    /**
     * Returns the path for the Process ID file
     *
     * @return string The path to the file
     */
    protected static function getProcessIdFilePath() : string
    {
        $currentDirectory = dirname(__FILE__);

        return $currentDirectory . '/' . self::BACKGROUND_SERVICE_PROCESS_INFO_FILE;
    }

    /**
     * Checks if the background service has been running for to long.
     * If it has then we kill it to avoid potential long-running issues
     *
     * @return bool True is the process was terminated, false otherwise
     */
    protected static function checkProcessRuntime() : bool
    {
        $processDetails          = self::getProcessDetails();
        $lastUpdateTime          = isset($processDetails['last_update_time']) ?
            (int) $processDetails['last_update_time'] : time();
        $latestAcceptableRunTime = strtotime(sprintf('-%d hours', self::MAX_PROCESS_RUN_TIME_IN_HOURS));
        if ($lastUpdateTime <= $latestAcceptableRunTime) {
            return self::killService();
        }

        return false;
    }

    /**
     * Determines if the polling background service is running
     *
     * @return bool True if running otherwise false
     */
    public static function isServiceRunning() : bool
    {
        return file_exists(self::getProcessIdFilePath());
    }

    /**
     * Starts the polling background service.
     *
     * @return bool True if the service was started otherwise false.
     */
    public static function startService() : bool
    {
        try {
            if (self::isServiceRunning() && ! self::checkProcessRuntime()) {
                return false;
            }
            $command        = self::getServiceCommand();
            $descriptorspec = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'a'],
            ];
            proc_open($command, $descriptorspec, $pipes);

            return true;
        } catch (Throwable $t) {
            return false;
        }
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

        try {
            $processId = self::getProcessId();
            posix_kill($processId, self::DEFAULT_KILL_SIGNAL);
            self::removeProcessIdFile();

            return true;
        } catch (Throwable $t) {
            return false;
        }
    }
}
