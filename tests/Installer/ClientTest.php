<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Tests\Installer;

use NS8\ProtectSDK\Http\Client as HttpClient;
use NS8\ProtectSDK\Installer\Client as InstallerClient;
use NS8\ProtectSDK\Installer\Exceptions\MissingData as MissingDataException;
use PHPUnit\Framework\TestCase;
use Zend\Http\Client as ZendClient;
use Zend\Http\Client\Adapter\Test as ZendTestAdapter;
use function json_encode;
use function sprintf;

/**
 * Uninstaller Test Class
 *
 * @coversDefaultClass NS8\ProtectSDK\Installer\Client
 */
class ClientTest extends TestCase
{
    /**
     * The InstallerClient Client instance we use for testing.
     *
     * @var InstallerClient
     */
    protected $installerClient;

    /**
     * Runs before every test.
     *
     * @return void
     */
    public function setUp() : void
    {
        $this->installerClient = new InstallerClient();
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
     */
    public function testGetAndSetHttpClient() : void
    {
        $httpClient = new HttpClient();
        $this->installerClient->setHttpClient($httpClient);
        $this->assertSame($httpClient, $this->installerClient->getHttpClient());
    }

    /**
     * Test installing.
     *
     * @return void
     *
     * @covers ::getHttpClient
     * @covers ::setHttpClient
     * @covers ::install
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\ManagerStructure::initConfiguration
     * @covers NS8\ProtectSDK\Http\Client::__construct
     * @covers NS8\ProtectSDK\Http\Client::executeJsonRequest
     * @covers NS8\ProtectSDK\Http\Client::executeRequest
     * @covers NS8\ProtectSDK\Http\Client::getAccessToken
     * @covers NS8\ProtectSDK\Http\Client::setSessionData
     * @covers NS8\ProtectSDK\Installer\Client::validateInstallDataArray
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::getLogLevelIntegerValue
     * @covers NS8\ProtectSDK\Logging\Client::info
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     * @covers NS8\ProtectSDK\Security\Client::getAuthUser
     * @covers NS8\ProtectSDK\Security\Client::getConfigManager
     * @covers NS8\ProtectSDK\Security\Client::getNs8AccessToken
     */
    public function testInstall() : void
    {
        $testPlatform = 'magento';
        $responseData = [
            'accessToken' => '123456',
            'queueId'   => '123456',
        ];

        $httpClientMock = $this->buildTestHttpClient($responseData);
        $this->installerClient->setHttpClient($httpClientMock);
        $installData = [
            'email' => '123@test.com',
            'storeUrl' => 'https://example.com',
        ];

        $installResponse = $this->installerClient->install('magento', $installData);
        $this->assertEquals($responseData['accessToken'], $installResponse['accessToken']);
    }

    /**
     * Test installing while missing required installation attributes.
     *
     * @return void
     *
     * @covers ::getHttpClient
     * @covers ::setHttpClient
     * @covers ::install
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\ManagerStructure::initConfiguration
     * @covers NS8\ProtectSDK\Http\Client::__construct
     * @covers NS8\ProtectSDK\Http\Client::executeJsonRequest
     * @covers NS8\ProtectSDK\Http\Client::executeRequest
     * @covers NS8\ProtectSDK\Http\Client::getAccessToken
     * @covers NS8\ProtectSDK\Http\Client::setSessionData
     * @covers NS8\ProtectSDK\Installer\Client::validateInstallDataArray
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::getLogLevelIntegerValue
     * @covers NS8\ProtectSDK\Logging\Client::info
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     * @covers NS8\ProtectSDK\Security\Client::getAuthUser
     * @covers NS8\ProtectSDK\Security\Client::getConfigManager
     * @covers NS8\ProtectSDK\Security\Client::getNs8AccessToken
     */
    public function testInstallMissingParams() : void
    {
        $testPlatform = 'magento';
        $responseData = [
            'accessToken' => '123456',
            'queueId'   => '123456',
        ];

        $httpClientMock = $this->buildTestHttpClient($responseData);
        $this->installerClient->setHttpClient($httpClientMock);

        $installData = ['email' => '123@test.com'];
        $this->expectException(MissingDataException::class);
        $installResponse = $this->installerClient->install('magento', $installData);

        $installData = ['storeUrl' => 'https://example.com'];
        $this->expectException(MissingDataException::class);
        $installResponse = $this->installerClient->install('magento', $installData);
    }

    /**
     * Returns a test Zend HTTP client to utilize when testing successful outputs
     *
     * @param mixed[] $data Array of data that should be present in JSON
     *
     * @return HttpClient
     */
    protected function buildTestHttpClient(array $data) : HttpClient
    {
        $adapter        = new ZendTestAdapter();
        $testHttpClient = new ZendClient(
            sprintf(InstallerClient::INSTALL_ENDPOINT, 'magento'),
            ['adapter' => $adapter]
        );

        $response =  'HTTP/1.1 200 OK' . "\n" .
        'Content-type: application/json' . "\n\n" .
        json_encode($data) .
        "\n";

        $adapter->setResponse($response);

        return new HttpClient(null, null, true, $testHttpClient);
    }
}
