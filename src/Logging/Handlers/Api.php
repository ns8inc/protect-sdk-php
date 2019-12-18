<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Logging\Handlers;

use Exception;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use NS8\ProtectSDK\Config\Manager as ConfigManager;
use NS8\ProtectSDK\Http\Client as httpClient;
use const PHP_OS;

/**
 * Dlass defining core logging functionality and expected behavior
 */
class Api extends AbstractProcessingHandler
{
    protected $initialized = false;
    protected $client;
    protected $statement;
    protected $configManager;

    protected static $selfCall;

    public function __construct(?httpClient $client = null, $level = Logger::DEBUG, bool $bubble = true)
    {
        $this->client        = $client ?? new httpClient();
        $this->configManager = new ConfigManager(null, null, null, null, null, true);
        parent::__construct($level, $bubble);
    }

    protected function write(array $record) : void
    {
        // Do not attempt to write API failures if the API failure was for logging remotely
        if (self::$selfCall) {
            self::$selfCall = false;

            return;
        }
        if (! $this->initialized) {
            $this->initialize();
        }

        $data = [
            'level' => $record['level_name'],
            'errString' =>  $record['message'],
            'stackTrace' => (new Exception())->getTraceAsString(),
            'category' => $this->configManager->getValue('logging.additional_info.category') . '_' . $this->configManager->getValue('logging.additional_info.integration_type'),
            'data' => [
                'platform' => $this->configManager->getValue('logging.additional_info.category'),
                'message' => $record['message'],
                'data' => $record['formatted'],
                'phpVersion' => $this->configManager->getValue('php_version'),
                'phpOS' => PHP_OS,
            ],
        ];

        self::$selfCall = true;
     //   $this->client->post('/util/log-client-error', $data);
        self::$selfCall = false;
    }

    private function initialize() : void
    {
        $this->initialized = true;
    }
}
