<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Tests\Logging\Handlers;

use Exception;
use Laminas\Http\Client as LaminasClient;
use Laminas\Http\Client\Adapter\Test as LaminasTestAdapter;
use NS8\ProtectSDK\Config\Manager as ConfigManager;
use NS8\ProtectSDK\Http\Client as HttpClient;
use NS8\ProtectSDK\Logging\Client as LoggingClient;
use NS8\ProtectSDK\Logging\Handlers\Api as ApiHandler;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * Logging Client Test Class
 *
 * @coversDefaultClass NS8\ProtectSDK\Logging\Handlers\Api
 */
class ApiTest extends TestCase
{
    /**
     * Test Logging Client constructor
     *
     * @return void
     *
     * @covers ::__construct
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     * @covers NS8\ProtectSDK\Config\ManagerStructure::initConfiguration
     * @covers NS8\ProtectSDK\Config\ManagerStructure::resetConfig
     * @covers NS8\ProtectSDK\Http\Client::__construct
     * @covers NS8\ProtectSDK\Http\Client::setAccessToken
     * @covers NS8\ProtectSDK\Http\Client::setAuthUsername
     * @covers NS8\ProtectSDK\Http\Client::setSessionData
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     **/
    public function testConstructor() : void
    {
        $this->assertInstanceOf(ApiHandler::class, new ApiHandler());
    }

    /**
     * Test API write logic during HTTP failure
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::getHttpClient
     * @covers ::setHttpClient
     * @covers ::write
     * @covers ::initialize
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\Manager::setRuntimeConfigValues
     * @covers NS8\ProtectSDK\Config\Manager::setValueWithoutValidation
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getEnvironment
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateConfigEnvRequirements
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     * @covers NS8\ProtectSDK\Config\ManagerStructure::setEnvironment
     * @covers NS8\ProtectSDK\Config\ManagerStructure::initConfiguration
     * @covers NS8\ProtectSDK\Config\ManagerStructure::resetConfig
     * @covers NS8\ProtectSDK\Security\Client::getAuthUser
     * @covers NS8\ProtectSDK\Security\Client::getConfigManager
     * @covers NS8\ProtectSDK\Security\Client::getNs8AccessToken
     * @covers NS8\ProtectSDK\Security\Client::validateAuthUser
     * @covers NS8\ProtectSDK\Security\Client::validateNs8AccessToken
     * @covers NS8\ProtectSDK\Http\Client::__construct
     * @covers NS8\ProtectSDK\Http\Client::executeJsonRequest
     * @covers NS8\ProtectSDK\Http\Client::executeRequest
     * @covers NS8\ProtectSDK\Http\Client::executeWithAuth
     * @covers NS8\ProtectSDK\Http\Client::getAccessToken
     * @covers NS8\ProtectSDK\Http\Client::getAuthUsername
     * @covers NS8\ProtectSDK\Http\Client::setSessionData
     * @covers NS8\ProtectSDK\Http\Client::getSessionData
     * @covers NS8\ProtectSDK\Http\Client::post
     * @covers NS8\ProtectSDK\Http\Client::setAccessToken
     * @covers NS8\ProtectSDK\Http\Client::setAuthUsername
     * @covers NS8\ProtectSDK\Http\Client::getPlatformIdentifier
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::error
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     * @covers NS8\ProtectSDK\Logging\Client::getLogLevelIntegerValue
     */
    public function testApiWriteFailure() : void
    {
        $configManager = new ConfigManager();
        $configManager->resetConfig();
        $configManager->initConfiguration();
        $configManager->setEnvironment('testing');
        $configManager->setValue('testing.authorization.auth_user', 'test');
        $configManager->setValue('testing.authorization.access_token', 'test');
        $configManager->setValue('logging.api.enabled', true);
        $testHttpClient = $this->getFailureClient();
        $ns8HttpClient  = new HttpClient(null, null, false, $testHttpClient);

        $logger         = new LoggingClient(null, $configManager);
        $testHttpClient = $this->getFailureClient();
        $ns8HttpClient  = new HttpClient(null, null, false, $testHttpClient, $configManager, $logger);

        $apiHandler = new ApiHandler();
        $apiHandler->setHttpClient($ns8HttpClient);
        $logger->addHandler($apiHandler);

        $this->expectException(Throwable::class);
        $ns8HttpClient->post('/test');
    }

    /**
     * Verify that stackTrace is an empty string (not null!) on non-errors.
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::getHttpClient
     * @covers ::setHttpClient
     * @covers ::write
     * @covers ::initialize
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\Manager::setRuntimeConfigValues
     * @covers NS8\ProtectSDK\Config\Manager::setValueWithoutValidation
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getEnvironment
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateConfigEnvRequirements
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     * @covers NS8\ProtectSDK\Config\ManagerStructure::setEnvironment
     * @covers NS8\ProtectSDK\Config\ManagerStructure::initConfiguration
     * @covers NS8\ProtectSDK\Config\ManagerStructure::resetConfig
     * @covers NS8\ProtectSDK\Security\Client::getAuthUser
     * @covers NS8\ProtectSDK\Security\Client::getConfigManager
     * @covers NS8\ProtectSDK\Security\Client::getNs8AccessToken
     * @covers NS8\ProtectSDK\Security\Client::validateAuthUser
     * @covers NS8\ProtectSDK\Security\Client::validateNs8AccessToken
     * @covers NS8\ProtectSDK\Http\Client::__construct
     * @covers NS8\ProtectSDK\Http\Client::executeJsonRequest
     * @covers NS8\ProtectSDK\Http\Client::executeRequest
     * @covers NS8\ProtectSDK\Http\Client::executeWithAuth
     * @covers NS8\ProtectSDK\Http\Client::getAccessToken
     * @covers NS8\ProtectSDK\Http\Client::getAuthUsername
     * @covers NS8\ProtectSDK\Http\Client::setSessionData
     * @covers NS8\ProtectSDK\Http\Client::getSessionData
     * @covers NS8\ProtectSDK\Http\Client::post
     * @covers NS8\ProtectSDK\Http\Client::setAccessToken
     * @covers NS8\ProtectSDK\Http\Client::setAuthUsername
     * @covers NS8\ProtectSDK\Http\Client::getPlatformIdentifier
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::error
     * @covers NS8\ProtectSDK\Logging\Client::info
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     * @covers NS8\ProtectSDK\Logging\Client::getLogLevelIntegerValue
     */
    public function testStackTraceIsNotNull() : void
    {
        $configManager = new ConfigManager();
        $configManager->resetConfig();
        $configManager->initConfiguration();
        $configManager->setEnvironment('testing');
        $configManager->setValue('testing.authorization.auth_user', 'test');
        $configManager->setValue('testing.authorization.access_token', 'test');
        $configManager->setValue('logging.api.enabled', true);
        $testHttpClient = $this->getFailureClient();
        $ns8HttpClient  = new HttpClient(null, null, false, $testHttpClient);
        $logger         = new LoggingClient(null, $configManager);
        $apiHandler     = new ApiHandler();
        $apiHandler->setHttpClient($ns8HttpClient);
        $logger->addHandler($apiHandler);
        $logger->info('Not a big deal');
        $this->assertEquals('', $testHttpClient->getRequest()->getPost('stackTrace'));
    }

    /**
     * Return HTTP client that will fail all requests
     *
     * @return LaminasClient HTTP client to be used in NS8 HTTP client set-up
     */
    protected function getFailureClient() : LaminasClient
    {
        $adapter = new LaminasTestAdapter();
        $adapter = new class extends LaminasTestAdapter {
            /**
             * Overrides connect function to gurantee connection will always fail
             *
             * @param mixed $host   Host to connect to
             * @param mixed $port   Port to connect to
             * @param mixed $secure Determines if secure transport should be set
             *
             * @return mixed
             */
            public function connect($host, $port = 80, $secure = false)
            {
                throw new Exception('Request failed');
            }
        };

        $response =  'HTTP/1.1 200 OK' . "\n" .
        'Content-type: application/json' . "\n\n" .
        '{' .
        '   "logged": true' .
        "}\n";
        $adapter->setResponse($response);

        return new LaminasClient('/path', ['adapter' => $adapter]);
    }
}
