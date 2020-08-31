<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Logging\Handlers;

use Exception;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use NS8\ProtectSDK\Config\Manager as ConfigManager;
use NS8\ProtectSDK\Http\Client as HttpClient;
use Throwable;
use const PHP_OS;

/**
 * Dlass defining core logging functionality and expected behavior
 */
class Api extends AbstractProcessingHandler
{
    /**
     * API route we are sending log data to
     */
    const LOGGING_PATH = '/util/log-platform-error';
    /**
     * Sets if the handler instance has been initialized
     *
     * @var bool
     */
    protected $initialized = false;

    /**
     * Client used for posting data to the API
     *
     * @var HttpClient
     */
    protected $client;

    /**
     * Config manager user to track settings
     *
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * Static attribute to ensure we do not call error handler recursively
     *
     * @var bool
     */
    protected static $selfCall;

    /**
     * Constructor for API handler
     *
     * @param ConfigManager $configManager Config manager we want to use for fetching settings
     * @param int           $level         Error level minimum required to use logger
     * @param bool          $bubble        Determines if messages should bubble up to the next handler
     */
    public function __construct(
        $configManager = null,
        int $level = Logger::DEBUG,
        bool $bubble = true
    ) {
        $this->configManager = $configManager ?? new ConfigManager();
        $this->configManager::initConfiguration();
        parent::__construct($level, $bubble);
    }

    /**
     * Sets the HTTP client to be used by the object for posting data to NS8
     *
     * @param HttpClient $httpClient HTTP Client we intend to use for posting data
     *
     * @return Api returns self for use in series
     */
    public function setHttpClient(HttpClient $httpClient) : Api
    {
        $this->client = $httpClient;

        return $this;
    }

    /**
     * Returns an HTTP client to be used by the Handler
     *
     * @return HttpClient NS8 HTTP Client used for sending data
     */
    public function getHttpClient() : HttpClient
    {
        return $this->client ?? new HttpClient();
    }

    /**
     * Write method used by Monologger which sends data to the NS8 API
     *
     * @param mixed[] $record Record of data we are sending to the API
     *
     * @return void
     */
    protected function write(array $record)
    {
        $this->client = $this->getHttpClient();
        try {
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
                'category' => $this->configManager->getValue('logging.additional_info.category')
                . '_'
                . $this->configManager->getValue('logging.additional_info.integration_type'),
                'data' => [
                    'platform' => $this->configManager->getValue('logging.additional_info.category'),
                    'message' => $record['message'],
                    'data' => $record['formatted'],
                    'phpVersion' => $this->configManager->getValue('php_version'),
                    'phpOS' => PHP_OS,
                ],
                'stackTrace' => '',
            ];

            if ($record['level_name'] === 'ERROR') {
                $data['stackTrace'] = (new Exception())->getTraceAsString();
            }

            self::$selfCall = true;
            $this->client->post(self::LOGGING_PATH, $data);
        } catch (Throwable $t) {
            // Silently fail so we avoid further exceptions due to logging
        }
        self::$selfCall = false;
    }

    /**
     * Initialize function to let Monologger know the handler has been initialized
     *
     * @return void
     */
    private function initialize()
    {
        $this->initialized = true;
    }
}
