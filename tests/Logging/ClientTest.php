<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Tests\Logging;

use Exception;
use Monolog\Handler\NullHandler as MonologNullHandler;
use NS8\ProtectSDK\Config\Manager as ConfigManager;
use NS8\ProtectSDK\Logging\Client as LoggingClient;
use PHPUnit\Framework\TestCase;
use function dirname;
use function file_exists;
use function file_get_contents;
use function sprintf;
use function unlink;

/**
 * Logging CLient Test Class
 *
 * @coversDefaultClass NS8\ProtectSDK\Logging\Client
 */
class ClientTest extends TestCase
{
    /**
     * Test Logging Client constructor
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::addHandler
     * @covers ::setApiHandler
     * @covers ::setStreamHandler
     * @covers ::getLogLevelIntegerValue
     * @covers NS8\ProtectSDK\Logging\Handlers\Api::__construct
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\Manager::setValueWithoutValidation
     * @covers NS8\ProtectSDK\Config\Manager::setRuntimeConfigValues
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     * @covers NS8\ProtectSDK\Config\ManagerStructure::initConfiguration
     * @covers NS8\ProtectSDK\Config\ManagerStructure::resetConfig
     * @covers NS8\ProtectSDK\Http\Client::__construct
     * @covers NS8\ProtectSDK\Http\Client::setAccessToken
     * @covers NS8\ProtectSDK\Http\Client::setAuthUsername
     * @covers NS8\ProtectSDK\Http\Client::setSessionData
     */
    public function testConstructor() : void
    {
        // Ensure all handlers are enabled in Constructor call
        $configManager = new ConfigManager();
        $configManager->resetConfig();
        $configManager->initConfiguration();
        $configManager->setValue('logging.file.enabled', true);
        $configManager->setValue('logging.api.enabled', true);

        $this->assertInstanceOf(LoggingClient::class, new LoggingClient(null, $configManager));
    }

    /**
     * Test debug writing method
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::debug
     * @covers ::addHandler
     * @covers ::setApiHandler
     * @covers ::setStreamHandler
     * @covers ::getLogLevelIntegerValue
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\Manager::setRuntimeConfigValues
     * @covers NS8\ProtectSDK\Config\Manager::setValueWithoutValidation
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     * @covers NS8\ProtectSDK\Config\ManagerStructure::initConfiguration
     * @covers NS8\ProtectSDK\Config\ManagerStructure::resetConfig
     */
    public function testDebugWriting() : void
    {
        $configManager = $this->getConfigManager();
        $logger        = new LoggingClient(null, $configManager);
        $statement     = 'This is a debug statement';
        $logger->debug($statement);
        $fileData = file_get_contents($this->getTestLogFilePath());
        $this->assertRegexp(sprintf('/%s/', $statement), $fileData);
    }

    /**
     * Test warn writing method
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::warn
     * @covers ::addHandler
     * @covers ::setApiHandler
     * @covers ::setStreamHandler
     * @covers ::getLogLevelIntegerValue
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\Manager::setRuntimeConfigValues
     * @covers NS8\ProtectSDK\Config\Manager::setValueWithoutValidation
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     * @covers NS8\ProtectSDK\Config\ManagerStructure::initConfiguration
     * @covers NS8\ProtectSDK\Config\ManagerStructure::resetConfig
     */
    public function testWarnWriting() : void
    {
        $configManager = $this->getConfigManager();
        $logger        = new LoggingClient(null, $configManager);
        $statement     = 'This is a warning statement';
        $logger->warn($statement);
        $fileData = file_get_contents($this->getTestLogFilePath());
        $this->assertRegexp(sprintf('/%s/', $statement), $fileData);
    }

    /**
     * Test info writing method
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::info
     * @covers ::addHandler
     * @covers ::setApiHandler
     * @covers ::setStreamHandler
     * @covers ::getLogLevelIntegerValue
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\Manager::setRuntimeConfigValues
     * @covers NS8\ProtectSDK\Config\Manager::setValueWithoutValidation
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     * @covers NS8\ProtectSDK\Config\ManagerStructure::initConfiguration
     * @covers NS8\ProtectSDK\Config\ManagerStructure::resetConfig
     */
    public function testInfoWriting() : void
    {
        $configManager = $this->getConfigManager();
        $logger        = new LoggingClient(null, $configManager);
        $statement     = 'This is an info statement';
        $logger->info($statement);
        $fileData = file_get_contents($this->getTestLogFilePath());
        $this->assertRegexp(sprintf('/%s/', $statement), $fileData);
    }

    /**
     * Test error writing method
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::error
     * @covers ::addHandler
     * @covers ::setApiHandler
     * @covers ::setStreamHandler
     * @covers ::getLogLevelIntegerValue
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\Manager::setRuntimeConfigValues
     * @covers NS8\ProtectSDK\Config\Manager::setValueWithoutValidation
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     * @covers NS8\ProtectSDK\Config\ManagerStructure::initConfiguration
     * @covers NS8\ProtectSDK\Config\ManagerStructure::resetConfig
     */
    public function testErrorWriting() : void
    {
        $configManager = $this->getConfigManager();
        $logger        = new LoggingClient(null, $configManager);
        $statement     = 'This is an info statement';
        $logger->error($statement, (new Exception()));
        $fileData = file_get_contents($this->getTestLogFilePath());
        $this->assertRegexp(sprintf('/%s/', $statement), $fileData);
    }

    /**
     * Test if file logging occurrs when it is disabled
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::debug
     * @covers ::addHandler
     * @covers ::setApiHandler
     * @covers ::setStreamHandler
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\Manager::setRuntimeConfigValues
     * @covers NS8\ProtectSDK\Config\Manager::setValueWithoutValidation
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\Manager::getConfigByFile
     * @covers NS8\ProtectSDK\Config\Manager::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     * @covers NS8\ProtectSDK\Config\ManagerStructure::initConfiguration
     * @covers NS8\ProtectSDK\Config\ManagerStructure::resetConfig
     */
    public function testFileLogWithConfigDisabled() : void
    {
        $configManager = $this->getConfigManager();
        $configManager->setValue('logging.file.enabled', false);
        $logger = new LoggingClient(null, $configManager);

        // Add null handler to prevent monolog from writing to console since no handlers are set at this stage
        $logger->addHandler(new MonologNullHandler());
        $logger->debug('Test');
        $logFileExists = file_exists($this->getTestLogFilePath());
        $this->assertEquals(false, $logFileExists);
    }

    /**
     * Sets up Config Manager used for testing
     *
     * @return ConfigManager
     */
    public static function getConfigManager() : ConfigManager
    {
        $configManager = new ConfigManager();
        $configManager->resetConfig();
        $configManager->initConfiguration();
        $configManager->setValue('logging.file.log_level', 'debug');
        $configManager->setValue('logging.file.relative_path', 'logs/unit_tests_log.log');
        $configManager->setValue('logging.api.enabled', false);
        $configManager->setValue('logging.file.enabled', true);

        return $configManager;
    }

    /**
     * Remove log file we wrote to
     *
     * @return void
     */
    protected function tearDown() : void
    {
        $filePath = $this->getTestLogFilePath();
        if (! file_exists($filePath)) {
            return;
        }

        unlink($filePath);
    }

    /**
     * Returns the default log file path
     *
     * @return string File path for loggs
     */
    protected function getTestLogFilePath() : string
    {
        return dirname(__FILE__) . '/../../logs/unit_tests_log.log';
    }
}
