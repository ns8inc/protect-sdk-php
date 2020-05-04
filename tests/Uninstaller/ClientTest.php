<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Tests\Uninstaller;

use NS8\ProtectSDK\Actions\Client as ActionsClient;
use NS8\ProtectSDK\Http\Client as HttpClient;
use NS8\ProtectSDK\Uninstaller\Client as UninstallerClient;
use PHPUnit\Framework\TestCase;

/**
 * Uninstaller Test Class
 *
 * @coversDefaultClass NS8\ProtectSDK\Uninstaller\Client
 */
class ClientTest extends TestCase
{
    /**
     * The Uninstaller Client instance we use for testing.
     *
     * @var UninstallerClient
     */
    protected $uninstallerClient;

    /**
     * Runs before every test.
     *
     * @return void
     */
    public function setUp() : void
    {
        $this->uninstallerClient = new UninstallerClient();
    }

    /**
     * Test getting/setting the HTTP client.
     *
     * @return void
     *
     * @covers ::getHttpClient
     * @covers ::setHttpClient
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
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
     * @covers NS8\ProtectSDK\Uninstall\Client::getHttpClient
     * @covers NS8\ProtectSDK\Uninstall\Client::setHttpClient
     */
    public function testGetAndSetHttpClient() : void
    {
        $httpClient = new HttpClient();
        $this->uninstallerClient->setHttpClient($httpClient);
        $this->assertSame($httpClient, $this->uninstallerClient->getHttpClient());
    }

    /**
     * Test uninstalling.
     *
     * @return void
     *
     * @covers ::getHttpClient
     * @covers ::setHttpClient
     * @covers ::uninstall
     */
    public function testUninstall() : void
    {
        $params         = ['action' => ActionsClient::UNINSTALL_ACTION];
        $response       = (object) ['foo' => 'bar'];
        $httpClientMock = $this->createMock(HttpClient::class);
        $this->uninstallerClient->setHttpClient($httpClientMock);

        $httpClientMock->expects($this->once())
            ->method('post')
            ->with(ActionsClient::SWITCH_EXECUTOR_PATH, [], $params)
            ->willReturn($response);

        $this->assertEquals($response, $this->uninstallerClient->uninstall());
    }
}
