<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Logging;

use Monolog\Handler\AbstractHandler as AbstractMonologHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use NS8\ProtectSDK\Config\Manager as ConfigManager;
use NS8\ProtectSDK\Logging\Handlers\Api as ApiHandler;
use Throwable;
use function array_key_exists;
use function dirname;
use function strtoupper;

/**
 * Dlass defining core logging functionality and expected behavior
 */
class Client extends ClientBase
{
    /**
     * Logging Channel for Monolog Usage
     */
    const LOGGER_CHANNEL_NAME = 'ns8';

    /**
     * Debug Level Info For Logging
     */
    const LOG_LEVEL_DEBUG_NAME          = 'DEBUG';
    const LOG_LEVEL_DEBUG_INTEGER_VALUE = 100;

    /**
     * Info Level Info For Logging
     */
    const LOG_LEVEL_INFO_NAME          = 'INFO';
    const LOG_LEVEL_INFO_INTEGER_VALUE = 200;

    /**
     * Notice Level Info For Logging
     */
    const LOG_LEVEL_NOTICE_NAME          = 'NOTICE';
    const LOG_LEVEL_NOTICE_INTEGER_VALUE = 250;

    /**
     * Warning Level Info For Logging
     */
    const LOG_LEVEL_WARNING_NAME          = 'WARNING';
    const LOG_LEVEL_WARNING_INTEGER_VALUE = 300;

    /**
     * Error Level Info For Logging
     */
    const LOG_LEVEL_ERROR_NAME          = 'ERROR';
    const LOG_LEVEL_ERROR_INTEGER_VALUE = 400;

    /**
     * Critical Level Info For Logging
     */
    const LOG_LEVEL_CRITICAL_NAME    = 'CRITICAL';
    const LOG_LEVEL_CRITICAL_INTEGER = 500;

    /**
     * Alert Level Info For Logging
     */
    const LOG_LEVEL_ALERT_NAME    = 'ALERT';
    const LOG_LEVEL_ALERT_INTEGER = 550;

    /**
     * Emergency Level Info For Logging
     */
    const LOG_LEVEL_EMERGENCY_NAME    = 'EMERGENCY';
    const LOG_LEVEL_EMERGENCY_INTEGER = 600;

    /**
     * Map log levels to their integer values
     */
    const LOG_LEVEL_MAPPING = [
        self::LOG_LEVEL_DEBUG_NAME => self::LOG_LEVEL_DEBUG_INTEGER_VALUE,
        self::LOG_LEVEL_INFO_NAME => self::LOG_LEVEL_INFO_INTEGER_VALUE,
        self::LOG_LEVEL_NOTICE_NAME => self::LOG_LEVEL_NOTICE_INTEGER_VALUE,
        self::LOG_LEVEL_WARNING_NAME => self::LOG_LEVEL_ERROR_INTEGER_VALUE,
        self::LOG_LEVEL_ERROR_NAME => self::LOG_LEVEL_ERROR_INTEGER_VALUE,
        self::LOG_LEVEL_ALERT_NAME => self::LOG_LEVEL_ALERT_INTEGER,
        self::LOG_LEVEL_EMERGENCY_NAME => self::LOG_LEVEL_EMERGENCY_INTEGER,
    ];

    /**
     * Logging client used to write to logs
     *
     * @var Logger
     */
    protected $logger;

    /**
     * Config Manager used to fetch settings
     *
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * Constructor to initialize logging client
     *
     * @param ?Logger        $logger        Logging object to be used by the client
     * @param ?ConfigManager $configManager Config manager used to fetch settings
     */
    public function __construct($logger = null, $configManager = null)
    {
        $this->logger        = $logger ?? new Logger(self::LOGGER_CHANNEL_NAME);
        $this->configManager = $configManager ?? new ConfigManager();
        $this->configManager::initConfiguration();
        $this->setApiHandler();
        $this->setStreamHandler();
    }

    /**
     * Add a handler to our logging stack
     *
     * @param AbstractMonologHandler $handler The handler we are adding to our logger
     *
     * @return void
     */
    public function addHandler(AbstractMonologHandler $handler)
    {
        $this->logger->pushHandler($handler);
    }

    /**
     * Log an error statement to all handlers
     *
     * @param string    $message The message we are logging
     * @param Throwable $event   Event (Error or Exception) associated with the error
     * @param mixed[]   $data    Additional data we are logging for the message
     *
     * @return void
     */
    public function error(string $message, $event = null, $data = null)
    {
        $data = (array) $data;
        if (isset($event)) {
            $data['throwable_event_data'] = $event;
        }

        $this->logger->error($message, $data);
    }

    /**
     * Log an debugging statement to all handlers
     *
     * @param string  $message The message we are logging
     * @param mixed[] $data    Additional data we are logging for the message
     *
     * @return void
     */
    public function debug(string $message, $data = null)
    {
        $this->logger->debug($message, (array) $data);
    }

    /**
     * Log a warning statement to all handlers
     *
     * @param string  $message The message we are logging
     * @param mixed[] $data    Additional data we are logging for the message
     *
     * @return void
     */
    public function warn(string $message, $data = null)
    {
        $this->logger->warning($message, (array) $data);
    }

    /**
     * Log an info statement to all handlers
     *
     * @param string  $message The message we are logging
     * @param mixed[] $data    Additional data we are logging for the message
     *
     * @return void
     */
    public function info(string $message, $data = null)
    {
        $this->logger->info($message, (array) $data);
    }

    /**
     * Set file stream handler if it is enabled
     *
     * @return Client returns self so it can be used in series
     */
    protected function setStreamHandler() : Client
    {
        $streamConfiguration = $this->configManager->getValue('logging.file');
        if (! $streamConfiguration['enabled']) {
            return $this;
        }

        $currentDirectory = dirname(__FILE__);
        $logPath          = $currentDirectory . '/../../' . $streamConfiguration['relative_path'];

        $streamHandler = new StreamHandler(
            $logPath,
            $this->getLogLevelIntegerValue((string) $streamConfiguration['log_level'])
        );
        $this->addHandler($streamHandler);

        return $this;
    }

    /**
     * Set API handler if it is enabled
     *
     * @return Client returns self so it can be used in series
     */
    protected function setApiHandler() : Client
    {
        $apiConfiguration = $this->configManager->getValue('logging.api');
        if (! $apiConfiguration['enabled']) {
            return $this;
        }

        $apiHandler = new ApiHandler(
            null,
            $this->getLogLevelIntegerValue((string) $apiConfiguration['log_level'])
        );
        $this->addHandler($apiHandler);

        return $this;
    }

    /**
     * Fetch log level value based on log level name
     *
     * @param string $logLevel The log level such as "Warning", "Error", etc. we want to fetch a value for
     *
     * @return int The integer value of the log level
     */
    protected function getLogLevelIntegerValue(string $logLevel) : int
    {
        $logLevel = strtoupper($logLevel);

        // If the level exists in our mapping, use that otherwise default to ERRORs only
        return array_key_exists($logLevel, self::LOG_LEVEL_MAPPING) ?
        self::LOG_LEVEL_MAPPING[$logLevel] : self::LOG_LEVEL_ERROR_INTEGER_VALUE;
    }
}
