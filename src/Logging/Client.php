<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Logging;

use Monolog\Handler\AbstractHandler as AbstractMonologHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use NS8\ProtectSDK\Config\Manager as ConfigManager;
use NS8\ProtectSDK\Logging\Handlers\Api as ApiHandler;
use Throwable;
use function dirname;

/**
 * Dlass defining core logging functionality and expected behavior
 */
class Client extends ClientBase
{
    public const LOGGER_CHANNEL_NAME = 'ns8';
    /**
     * Logging client used to write to logs
     *
     * @var Logger $logger
     */
    protected $logger;

    /**
     * Config Manager used to fetch settings
     *
     * @var ConfigManager $configManager
     */
    protected $configManager;

    /**
     * Constructor to initialize logging client
     *
     * @param ?Logger        $logger        Logging object to be used by the client
     * @param ?ConfigManager $configManager Config manager usrd to fetch settings
     */
    public function __construct(?Logger $logger = null, ?ConfigManager $configManager = null)
    {
        $this->logger        = $logger ?? new Logger(self::LOGGER_CHANNEL_NAME);
        $this->configManager = $configManager ?? new ConfigManager();
        $this->setStreamHandler();
        $this->setApiHandler();
    }

    /**
     * Add a jandler to our logging stack
     *
     * @param AbstractMonologHandler $handler The handler we are adding to our logger
     *
     * @return void
     */
    public function addHandler(AbstractMonologHandler $handler) : void
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
    public function error(string $message, ?Throwable $event = null, ?array $data = null) : void
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
    public function debug(string $message, ?array $data = null) : void
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
    public function warn(string $message, ?array $data = null) : void
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
    public function info(string $message, ?array $data = null) : void
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
        $streamHandler    = new StreamHandler($logPath, $streamConfiguration['log_level']);
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

        $this->addHandler(new ApiHandler(null, $apiConfiguration['log_level']));

        return $this;
    }
}
