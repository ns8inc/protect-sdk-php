<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Tests\Analytics;

use NS8\ProtectSDK\Analytics\Client as AnalyticsClient;
use NS8\ProtectSDK\Config\Manager as ConfigManager;
use NS8\ProtectSDK\Http\Client as HttpClient;
use PHPUnit\Framework\TestCase;
use Zend\Http\Client as ZendClient;
use Zend\Http\Client\Adapter\Test as ZendTestAdapter;
use function json_encode;

/**
 * Analytics Test Class
 *
 * @coversDefaultClass NS8\ProtectSDK\Analytics\Client
 */
class ClientTest extends TestCase
{
    /**
     * Attribute to track config manager
     *
     * @var ConfigManager $configManager Config manager used to manage settings during tests
     */
    protected static $configManager;

    /**
     * Test value returned for fetching the True Stats route
     *
     * @return void
     *
     * @covers ::getTrueStatsRoute
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::setValueWithoutValidation
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\ManagerStructure::initConfiguration
     */
    public function testGetTrueStatsUrl() : void
    {
        $trueStatsRoute = AnalyticsClient::getTrueStatsRoute();
        $this->assertEquals(AnalyticsClient::TRUE_STATS_ROUTE, $trueStatsRoute);
    }

    /**
     * Test vale return for fetching the True Stats route
     *
     * @return void
     *
     * @covers ::getTrueStatsRoute
     * @covers ::getTrueStatsRoute
     * @covers ::getTrueStatsScript
     * @covers ::setHttpClient
     * @covers ::getHttpClient
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::setValueWithoutValidation
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\ManagerStructure::initConfiguration
     * @covers NS8\ProtectSDK\Http\Client::__construct
     * @covers NS8\ProtectSDK\Http\Client::executeRequest
     * @covers NS8\ProtectSDK\Http\Client::getAccessToken
     * @covers NS8\ProtectSDK\Http\Client::sendNonObjectRequest
     * @covers NS8\ProtectSDK\Http\Client::setAccessToken
     * @covers NS8\ProtectSDK\Http\Client::setAuthUsername
     * @covers NS8\ProtectSDK\Http\Client::setSessionData
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::getLogLevelIntegerValue
     * @covers NS8\ProtectSDK\Logging\Client::info
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     * @covers NS8\ProtectSDK\Security\Client::getAuthUser
     * @covers NS8\ProtectSDK\Security\Client::getConfigManager
     * @covers NS8\ProtectSDK\Security\Client::getNs8AccessToken
     * @covers NS8\ProtectSDK\Security\Client::validateNs8AccessToken
     */
    public function testGetTrueStatsJs() : void
    {
        $trueStatsRoute = AnalyticsClient::getTrueStatsRoute();
        $this->assertEquals(AnalyticsClient::TRUE_STATS_ROUTE, $trueStatsRoute);

        $httpClient = new HttpClient(null, null, true, $this->buildTestHttpClient());
        AnalyticsClient::setHttpClient($httpClient);
        $script = AnalyticsClient::getTrueStatsScript();
        $this->assertEquals($this->getTestHttpResponseBody(), $script);
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
        'Content-type: application/javascript' . "\n\n" .
        json_encode($this->getTestHttpResponseBody()) .
        "\n";

        $adapter->setResponse($response);

        return $testHttpClient;
    }

    /**
     * Returns the test HTTP response body
     *
     * @return string Value in mock HTTP response
     */
    protected function getTestHttpResponseBody() : string
    {
        return '<script>console.log("Hello");</script>';
    }
}
