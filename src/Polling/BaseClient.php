<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Polling;

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
     * Retrieve and store process details from Process Id File Path
     *
     * @return mixed[] Details regarding the process
     */
    abstract protected static function getProcessDetails() : array;

    /**
     * Return the process ID of the background polling process
     *
     * @return int|null The process ID as an integer
     */
    abstract protected static function getProcessId() : ?int;

    /**
     * Returns the path for the PHP binary executable on the current system
     *
     * @return string The path for the PHP executable
     */
    abstract protected static function getPHPBinaryPath() : string;

    /**
     * Removes the file storing the process ID
     *
     * @return void
     */
    abstract protected static function removeProcessIdFile() : void;

    /**
     * Returns the command used to execute the background polling service
     *
     * @return string The command that is needed to begin polling
     */
    abstract protected static function getServiceCommand() : string;

    /**
     * Returns the path for the Process ID file
     *
     * @return string The path to the file
     */
    abstract protected static function getProcessIdFilePath() : string;

    /**
     * Checks if the background service has been running for an hour or longer.
     * If it has then we kill it to avoid potential long-running issues
     *
     * @return bool True is the process was terminated, false otherwise
     */
    abstract protected static function checkProcessRuntime() : bool;

    /**
     * Determines if the polling background service is running
     *
     * @return bool True if running otherwise false
     */
    abstract public static function isServiceRunning() : bool;

    /**
     * Starts the polling background service.
     *
     * @return bool True if the service was started otherwise false.
     */
    abstract public static function startService() : bool;

    /**
     * Stops the polling background service from running and removes the Process ID File
     *
     * @return bool True if the service was successfully stopped, otherwise false.
     */
    abstract public static function killService() : bool;
}
