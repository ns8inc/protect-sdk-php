<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Tests\Analytics;

use NS8\ProtectSDK\Analytics\Client as AnalyticsClient;
use NS8\ProtectSDK\Config\Manager as ConfigManager;
use NS8\ProtectSDK\Http\Client as HttpClient;
use PHPUnit\Framework\TestCase;
use function file_exists;
use function file_put_contents;
use function json_encode;
use function sys_get_temp_dir;
use function time;
use function touch;
use function unlink;

/**
 * Analytics Test Class
 *
 * @coversDefaultClass NS8\ProtectSDK\Analytics\Client
 */
class ClientTest extends TestCase
{
    /**
     * The Analytics Client instance we use for testing.
     *
     * @var AnalyticsClient
     */
    protected $analyticsClient;

    /**
     * Attribute to track config manager
     *
     * @var ConfigManager $configManager Config manager used to manage settings during tests
     */
    protected static $configManager;

    /**
     * Runs before every test.
     *
     * @return void
     */
    public function setUp() : void
    {
        $this->analyticsClient = new AnalyticsClient();
        self::$configManager   = new ConfigManager();
        self::$configManager->initConfiguration('testing');
        self::$configManager->setValue('testing.authorization.auth_user', 'test');
        self::$configManager->setValue('testing.authorization.access_token', 'test');
    }

    /**
     * Cleans up after each test
     *
     * @return void
     */
    public function tearDown() : void
    {
        $cacheFile = $this->getCacheFile();

        if (! file_exists($cacheFile)) {
            return;
        }

        unlink($cacheFile);
    }

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
     * Test value return for fetching the True Stats script (cached)
     *
     * @return void
     *
     * @covers ::getFullPathToScriptCacheFile
     * @covers ::getHttpClient
     * @covers ::getScriptFromCache
     * @covers ::getTrueStatsRoute
     * @covers ::getTrueStatsScript
     * @covers ::setHttpClient
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
    public function testGetTrueStatsScriptCached() : void
    {
        // Store our script in the cache
        file_put_contents($this->getCacheFile(), $this->getTestHttpResponseBody());

        // Mock the HTTP client so we can make sure it doesn't make any requests
        $httpClientMock = $this->createMock(HttpClient::class);
        $this->analyticsClient->setHttpClient($httpClientMock);

        $httpClientMock->expects($this->never())
            ->method('sendNonObjectRequest');

        // Fetch the script, which should come from the cache
        $this->assertEquals($this->getTestHttpResponseBody(), $this->analyticsClient->getTrueStatsScript());
    }

    /**
     * Test value return for fetching the True Stats script (expired cache)
     *
     * @return void
     *
     * @covers ::getFullPathToScriptCacheFile
     * @covers ::getHttpClient
     * @covers ::getScriptFromCache
     * @covers ::getTrueStatsRoute
     * @covers ::getTrueStatsScript
     * @covers ::saveScriptToCache
     * @covers ::setHttpClient
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
    public function testGetTrueStatsScriptExpiredCache() : void
    {
        // Store our script in the cache
        $cacheFile = $this->getCacheFile();
        file_put_contents($cacheFile, $this->getTestHttpResponseBody());

        // Back-date the cache file's timestamp beyond the TTL
        touch($cacheFile, time() - 100000);

        // Mock the HTTP client so we can make sure it really makes a request
        $httpClientMock = $this->createMock(HttpClient::class);
        $this->analyticsClient->setHttpClient($httpClientMock);

        $httpClientMock->expects($this->once())
            ->method('sendNonObjectRequest')
            ->with('/init/script')
            ->willReturn(json_encode($this->getTestHttpResponseBody()));

        // Fetch the script, which should come from the cache
        $this->assertEquals($this->getTestHttpResponseBody(), $this->analyticsClient->getTrueStatsScript());
    }

    /**
     * Test value return for fetching the True Stats script (uncached)
     *
     * @return void
     *
     * @covers ::getFullPathToScriptCacheFile
     * @covers ::getHttpClient
     * @covers ::getScriptFromCache
     * @covers ::getTrueStatsRoute
     * @covers ::getTrueStatsScript
     * @covers ::saveScriptToCache
     * @covers ::setHttpClient
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
    public function testGetTrueStatsScriptUncached() : void
    {
        // Mock the HTTP client so we can make sure it really makes a request
        $httpClientMock = $this->createMock(HttpClient::class);
        $this->analyticsClient->setHttpClient($httpClientMock);

        $httpClientMock->expects($this->once())
            ->method('sendNonObjectRequest')
            ->with('/init/script')
            ->willReturn(json_encode($this->getTestHttpResponseBody()));

        // Fetch the script, which should not come from the cache
        $script = $this->analyticsClient->getTrueStatsScript();
        $this->assertEquals($this->getTestHttpResponseBody(), $script);

        // The script should now be cached in a temporary file
        $this->assertTrue(file_exists($this->getCacheFile()));
    }

    /**
     * Assert that the script doesn't get cached in case of a Protect error
     *
     * @return void
     *
     * @covers ::getFullPathToScriptCacheFile
     * @covers ::getHttpClient
     * @covers ::getScriptFromCache
     * @covers ::getTrueStatsRoute
     * @covers ::getTrueStatsScript
     * @covers ::setHttpClient
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
    public function testGetTrueStatsScriptDontCacheOnError() : void
    {
        // Mock the HTTP client so we can make sure it doesn't make any requests
        $httpClientMock = $this->createMock(HttpClient::class);
        $this->analyticsClient->setHttpClient($httpClientMock);

        // Protect errors get returned as double-encoded JSON
        $httpClientMock->expects($this->once())
            ->method('sendNonObjectRequest')
            ->with('/init/script')
            ->willReturn(json_encode($this->getTestHttpResponseError()));

        // Fetch the script, which should not come from the cache
        $script = $this->analyticsClient->getTrueStatsScript();
        $this->assertEquals($this->getTestHttpResponseError(), $script);

        // Because of the error, the script should not get cached.
        $this->assertFalse(file_exists($this->getCacheFile()));
    }

    /**
     * Returns the file name used for caching the TrueStats script
     *
     * @return string The file name
     */
    protected function getCacheFile() : string
    {
        return sys_get_temp_dir() . '/ns8-truestats.json';
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

    /**
     * Returns the test HTTP response error
     *
     * @return string Value in mock HTTP response
     */
    protected function getTestHttpResponseError() : string
    {
        $error = [
            'error' => 'An error occurred.',
            'statusCode' => 400,
        ];

        return json_encode($error);
    }
}
