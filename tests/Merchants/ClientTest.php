<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Tests\Merchants;

use NS8\ProtectSDK\Http\Client as HttpClient;
use NS8\ProtectSDK\Merchants\Client as MerchantsClient;
use PHPUnit\Framework\TestCase;

/**
 * Merchants Test Class
 *
 * @coversDefaultClass NS8\ProtectSDK\Merchants\Client
 */
class ClientTest extends TestCase
{
    /**
     * The Merchants Client instance we use for testing.
     *
     * @var MerchantsClient
     */
    protected $merchantsClient;

    /**
     * Runs before every test.
     *
     * @return void
     */
    public function setUp() : void
    {
        $this->merchantsClient = new MerchantsClient();
    }

    /**
     * Test getting/setting the HTTP client.
     *
     * @return void
     *
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\ManagerStructure::initConfiguration
     * @covers NS8\ProtectSDK\Http\Client::__construct
     * @covers NS8\ProtectSDK\Http\Client::setAccessToken
     * @covers NS8\ProtectSDK\Http\Client::setAuthUsername
     * @covers NS8\ProtectSDK\Http\Client::setSessionData
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::getLogLevelIntegerValue
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     * @covers NS8\ProtectSDK\Logging\Handlers\Api::__construct
     * @covers NS8\ProtectSDK\Security\Client::getAuthUser
     * @covers NS8\ProtectSDK\Security\Client::getConfigManager
     * @covers NS8\ProtectSDK\Security\Client::getNs8AccessToken
     * @covers NS8\ProtectSDK\Merchants\Client::getHttpClient
     * @covers NS8\ProtectSDK\Merchants\Client::setHttpClient
     */
    public function testGetAndSetHttpClient() : void
    {
        $httpClient = new HttpClient();
        $this->merchantsClient->setHttpClient($httpClient);
        $this->assertSame($httpClient, $this->merchantsClient->getHttpClient());
    }

    /**
     * Test getting the current merchant.
     *
     * @return void
     *
     * @covers NS8\ProtectSDK\Merchants\Client::getCurrent
     * @covers NS8\ProtectSDK\Merchants\Client::getHttpClient
     * @covers NS8\ProtectSDK\Merchants\Client::setHttpClient
     */
    public function testGetCurrent() : void
    {
        $merchant       = (object) ['name' => 'Bob'];
        $httpClientMock = $this->createMock(HttpClient::class);
        $this->merchantsClient->setHttpClient($httpClientMock);

        $httpClientMock->expects($this->once())
            ->method('get')
            ->with('/merchant/current')
            ->willReturn($merchant);

        $this->assertEquals($merchant, $this->merchantsClient->getCurrent());
    }
}
