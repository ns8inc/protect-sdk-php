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
     * @var Monolog\Logger $logger
     */
    protected $logger;

    protected $configManager;

    public function __construct(?Logger $logger = null)
    {
        $this->logger = $logger ?? new Logger(self::LOGGER_CHANNEL_NAME);
        // TODO: Revert arguments for Config Manager when done testing
        $this->configManager = new ConfigManager(null, null, null, null, null, true);
        $this->setStreamHandler();
        $this->setApiHandler();
    }

    public function addHandler(AbstractMonologHandler $handler) : void
    {
        $this->logger->pushHandler($handler);
    }

    public function error(string $message, ?Throwable $event = null, ?array $data = null) : void
    {
        $data = (array) $data;
        if (isset($event)) {
            $data['throwable_event_data'] = $event;
        }

        $this->logger->error($message, $data);
    }

    public function debug(string $message, ?array $data = null) : void
    {
        $this->logger->debug($message, (array) $data);
    }

    public function warn(string $message, ?array $data = null) : void
    {
        $this->logger->warning($message, (array) $data);
    }

    public function info(string $message, ?array $data = null) : void
    {
        $this->logger->info($message, (array) $data);
    }

    protected function setStreamHandler() : void
    {
        $streamConfiguration = $this->configManager->getValue('logging.file');
        if (! $streamConfiguration['enabled']) {
            return;
        }

        $currentDirectory = dirname(__FILE__);
        $logPath          = $currentDirectory . '/../../' . $streamConfiguration['relative_path'];
        $streamHandler    = new StreamHandler($logPath, $streamConfiguration['log_level']);
       // $streamHandler->setFormatter(new MonologNormalizerFormatter());
        $this->addHandler($streamHandler);
    }

    protected function setApiHandler() : void
    {
        $apiConfiguration = $this->configManager->getValue('logging.api');
        if (! $apiConfiguration['enabled']) {
            return;
        }

        $this->addHandler(new ApiHandler(null, $apiConfiguration['log_level']));
    }
}
