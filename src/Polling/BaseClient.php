<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Polling;

use const PHP_BINARY;
use function dirname;
use function file_exists;
use function file_get_contents;
use function json_decode;
use function proc_open;
use function sprintf;
use function strtotime;
use function time;
use function unlink;

/**
 * Manage structure for background service polling logic
 */
abstract class BaseClient
{
    /**
     * The file where we are going to store the background Process ID
     */
    public const BACKGROUND_SERVICE_PROCESS_INFO_FILE = 'BACKGROUND_PROCESS_INFO';

    /**
     * The name of the polling script we want to run in the background
     */
    public const PHP_POLLING_SCRIPT = 'Script.php';

    /**
     * Default process kill signal
     */
    public const DEFAULT_KILL_SIGNAL = 9;

    /**
     * The max length we are comfortable with the process running for
     *
     * @var string $maxRunTimeDuration
     */
    protected static $maxRunTimeDuration = '1 hours';

    /**
     * Returns the command used to execute the background polling service
     *
     * @return string The command that is needed to begin polling
     */
    abstract protected static function getServiceCommand() : string;

    /**
     * Stops the polling background service from running and removes the Process ID File
     *
     * @return bool True if the service was successfully stopped, otherwise false.
     */
    abstract public static function killService() : bool;

    /**
     * Starts the polling background service.
     *
     * @return bool True if the service was started otherwise false.
     */
    public static function startService() : bool
    {
        if (static::isServiceRunning() && ! static::checkProcessRuntime()) {
            return false;
        }

        $command        = static::getServiceCommand();
        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'a'],
        ];

        proc_open($command, $descriptorspec, $pipes);

        return true;
    }

    /**
     * Retrieve and store process details from Process Id File Path
     *
     * @return mixed[] Details regarding the process
     */
    protected static function getProcessDetails() : array
    {
        if (! static::isServiceRunning()) {
            return [];
        }
        $processDetailsString = file_get_contents(self::getProcessIdFilePath());
        $processDetails       = json_decode($processDetailsString, true);

        return (array) $processDetails;
    }

    /**
     * Return the process ID of the background polling process
     *
     * @return int|null The process ID as an integer
     */
    public static function getProcessId() : ?int
    {
        $processDetails = static::getProcessDetails();

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
     * Returns the path for the Process ID file
     *
     * @return string The path to the file
     */
    public static function getProcessIdFilePath() : string
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
        $processDetails          = static::getProcessDetails();
        $lastUpdateTime          = isset($processDetails['last_update_time']) ?
            (int) $processDetails['last_update_time'] : time();
        $latestAcceptableRunTime = strtotime(sprintf('-%s', static::$maxRunTimeDuration));
        if ($lastUpdateTime <= $latestAcceptableRunTime) {
            return static::killService();
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
     * Return the max runtime we will tolerate the background polling process to run for
     *
     * @return string A string representing the max runtime we will tolerate
     */
    public static function getMaxRunTime() : string
    {
        return static::$maxRunTimeDuration;
    }

    /**
     * Set the max runtime we will tolerate the background polling process to run for
     *
     * @param string $maxRunTimeDuration The max runtime we will tolerate the background polling process
     *
     * @return void
     */
    public static function setMaxRunTime(string $maxRunTimeDuration) : void
    {
        static::$maxRunTimeDuration = $maxRunTimeDuration;
    }
}
