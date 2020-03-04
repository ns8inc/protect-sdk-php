<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Tests\Actions;

use NS8\ProtectSDK\Actions\Client as ActionClient;
use NS8\ProtectSDK\Config\Manager as ConfigManager;
use NS8\ProtectSDK\Http\Client as HttpClient;
use PHPUnit\Framework\TestCase;
use Zend\Http\Client as ZendClient;
use Zend\Http\Client\Adapter\Test as ZendTestAdapter;

/**
 * Actions Test Class
 *
 * @coversDefaultClass NS8\ProtectSDK\Actions\Client
 */
class ClientTest extends TestCase
{
    /**
     * Test status code to use in comparisons
     */
    public const TEST_STATUS_CODE = 200;

    /**
     * Attribute to track config manager
     *
     * @var ConfigManager $configManager Config manager used to manage settings during tests
     */
    protected static $configManager;

    /**
     * Test Set Action functionality
     *
     * @return void
     *
     * @covers ::getHttpClient
     * @covers ::setHttpClient
     * @covers ::setAction
     * @covers ::sendProtectData
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\Manager::setRuntimeConfigValues
     * @covers NS8\ProtectSDK\Config\Manager::setValueWithoutValidation
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     * @covers NS8\ProtectSDK\Config\ManagerStructure::initConfiguration
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
     * @covers NS8\ProtectSDK\Http\Client::getSessionData
     * @covers NS8\ProtectSDK\Http\Client::post
     * @covers NS8\ProtectSDK\Http\Client::setAccessToken
     * @covers NS8\ProtectSDK\Http\Client::setAuthUsername
     * @covers NS8\ProtectSDK\Http\Client::setSessionData
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::info
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     * @covers NS8\ProtectSDK\Logging\Client::getLogLevelIntegerValue
     */
    public function testSetAction() : void
    {
        $httpClient   = new HttpClient(null, null, true, $this->buildTestHttpClient());
        $actionClient = new ActionClient();
        $actionClient->setHttpClient($httpClient);
        $setActionResponse = $actionClient->setAction(ActionClient::CREATE_ORDER_ACTION, ['data_key' => 'data_value']);
        $this->assertEquals(true, $setActionResponse);
    }

    /**
     * Test Set Action functionality
     *
     * @return void
     *
     * @covers ::getHttpClient
     * @covers ::setHttpClient
     * @covers ::triggerEvent
     * @covers ::sendProtectData
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\Manager::setRuntimeConfigValues
     * @covers NS8\ProtectSDK\Config\Manager::setValueWithoutValidation
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     * @covers NS8\ProtectSDK\Config\ManagerStructure::initConfiguration
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
     * @covers NS8\ProtectSDK\Http\Client::getSessionData
     * @covers NS8\ProtectSDK\Http\Client::post
     * @covers NS8\ProtectSDK\Http\Client::setAccessToken
     * @covers NS8\ProtectSDK\Http\Client::setAuthUsername
     * @covers NS8\ProtectSDK\Http\Client::setSessionData
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::info
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     * @covers NS8\ProtectSDK\Logging\Client::getLogLevelIntegerValue
     */
    public function testTriggerEvent() : void
    {
        $httpClient   = new HttpClient(null, null, true, $this->buildTestHttpClient());
        $actionClient = new ActionClient();
        $actionClient->setHttpClient($httpClient);
        $setActionResponse = $actionClient->triggerEvent(
            ActionClient::CREATE_ORDER_ACTION,
            ['data_key' => 'data_value']
        );
        $this->assertEquals(true, $setActionResponse);
    }

    /**
     * Test Get Entity functionality
     *
     * @return void
     *
     * @covers ::getHttpClient
     * @covers ::setHttpClient
     * @covers ::getEntity
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\Manager::setRuntimeConfigValues
     * @covers NS8\ProtectSDK\Config\Manager::setValueWithoutValidation
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     * @covers NS8\ProtectSDK\Config\ManagerStructure::initConfiguration
     * @covers NS8\ProtectSDK\Security\Client::getAuthUser
     * @covers NS8\ProtectSDK\Security\Client::getConfigManager
     * @covers NS8\ProtectSDK\Security\Client::getNs8AccessToken
     * @covers NS8\ProtectSDK\Security\Client::validateNs8AccessToken
     * @covers NS8\ProtectSDK\Http\Client::__construct
     * @covers NS8\ProtectSDK\Http\Client::executeJsonRequest
     * @covers NS8\ProtectSDK\Http\Client::executeRequest
     * @covers NS8\ProtectSDK\Http\Client::executeWithAuth
     * @covers NS8\ProtectSDK\Http\Client::get
     * @covers NS8\ProtectSDK\Http\Client::getAccessToken
     * @covers NS8\ProtectSDK\Http\Client::setAccessToken
     * @covers NS8\ProtectSDK\Http\Client::setAuthUsername
     * @covers NS8\ProtectSDK\Http\Client::setSessionData
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::info
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     * @covers NS8\ProtectSDK\Logging\Client::getLogLevelIntegerValue
     */
    public function testGetAction() : void
    {
        $httpClient   = new HttpClient(null, null, true, $this->buildTestHttpClient());
        $actionClient = new ActionClient();
        $actionClient->setHttpClient($httpClient);
        $orderData = $actionClient->getEntity('/orders/order-name/TEST_DATA');
        $this->assertEquals(self::TEST_STATUS_CODE, $orderData->httpCode);
    }

    /**
     * Sets up Config Manager before a test is ran
     *
     * @return void
     */
    public function setUp() : void
    {
        self::$configManager = new ConfigManager();
        self::$configManager->initConfiguration('testing');
        self::$configManager->setValue('logging.api.enabled', false);
        self::$configManager->setValue('logging.file.enabled', true);
        self::$configManager->setValue('testing.authorization.auth_user', 'test');
        self::$configManager->setValue('testing.authorization.access_token', 'test');
    }

    /**
     * Returns a test Zend HTTP client to utilize when invoking the NS8 Core HTTP Client
     *
     * @return ZendClient
     */
    protected function buildTestHttpClient() : ZendClient
    {
        $adapter        = new ZendTestAdapter();
        $testHttpClient = new ZendClient('', ['adapter' => $adapter]);

        $response =  'HTTP/1.1 200 OK' . "\n" .
        'Content-type: application/json' . "\n\n" .
        '{' .
        '   "httpCode": ' . self::TEST_STATUS_CODE .
        "}\n";

        $adapter->setResponse($response);

        return $testHttpClient;
    }
}
