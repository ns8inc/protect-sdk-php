<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Tests\Security;

use NS8\ProtectSDK\Config\Manager as ConfigManager;
use NS8\ProtectSDK\Security\Client as SecurityClient;
use PHPUnit\Framework\TestCase;
use function get_class;

/**
 * Security Test Class
 *
 * @coversDefaultClass NS8\ProtectSDK\Security\Client
 */
class ClientTest extends TestCase
{
    /**
     * Attribute to track config manager
     *
     * @covers ::getConfigManager
     * @var ConfigManager $configManager Config manager used to manage settings during tests
     */
    protected static $configManager;

    /**
     * Test setting Config Manager
     *
     * @return void
     *
     * @covers ::setConfigManager
     * @covers ::getConfigManager
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     */
    public function testSetConfigManager() : void
    {
        SecurityClient::setConfigManager(self::$configManager);
        $this->assertEquals(self::$configManager, SecurityClient::getConfigManager());
    }

    /**
     * Test returning the Config Manager
     *
     * @return void
     *
     * @covers ::getConfigManager
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     */
    public function testGetConfigManager() : void
    {
        $configManager = SecurityClient::getConfigManager();
        $this->assertEquals(ConfigManager::class, get_class($configManager));
    }

    /**
     * Test getting the NS8 Access Token
     *
     * @return void
     *
     * @covers ::getConfigManager
     * @covers ::getNs8AccessToken
     * @covers ::setNs8AccessToken
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getEnvonment
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     */
    public function testGetNs8AccessToken() : void
    {
        $accessToken = 'Test Value';
        SecurityClient::setNs8AccessToken($accessToken);
        $accessTokenValue = SecurityClient::getNs8AccessToken();
        $this->assertEquals($accessToken, $accessTokenValue);
    }

    /**
     * Test setting the NS8 Access Token
     *
     * @return void
     *
     * @covers ::getConfigManager
     * @covers ::getNs8AccessToken
     * @covers ::setNs8AccessToken
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getEnvonment
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     */
    public function testSetNs8AccessToken() : void
    {
        $accessToken = 'Test Value';
        SecurityClient::setNs8AccessToken($accessToken);
        $accessTokenValue = SecurityClient::getNs8AccessToken();
        $this->assertEquals($accessToken, $accessTokenValue);

        $accessToken = 'New Test Value';
        SecurityClient::setNs8AccessToken($accessToken);
        $accessTokenValue = SecurityClient::getNs8AccessToken();
        $this->assertEquals($accessToken, $accessTokenValue);
    }

    /**
     * Test validating the NS8 Access Token
     *
     * @return void
     *
     * @covers ::getConfigManager
     * @covers ::validateNs8AccessToken
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getEnvonment
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     */
    public function testValidateNs8AccessToken() : void
    {
        $expectedFalse = SecurityClient::validateNs8AccessToken('');
        $this->assertEquals(false, $expectedFalse);

        $expectedTrue = SecurityClient::validateNs8AccessToken('ACCESS_TOKEN');
        $this->assertEquals(true, $expectedTrue);
    }

    /**
     * Test getting the Auth User
     *
     * @return void
     *
     * @covers ::getConfigManager
     * @covers ::setAuthUser
     * @covers ::getAuthUser
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getEnvonment
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     */
    public function testGetAuthUser() : void
    {
        $authUser = 'Test Value';
        SecurityClient::setAuthUser($authUser);
        $authUserValue = SecurityClient::getAuthUser();
        $this->assertEquals($authUser, $authUserValue);
    }

    /**
     * Test setting the Auth User
     *
     * @return void
     *
     * @covers ::getConfigManager
     * @covers ::setAuthUser
     * @covers ::getAuthUser
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getEnvonment
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     */
    public function testSetAuthUser() : void
    {
        $authUser = 'Test Value';
        SecurityClient::setAuthUser($authUser);
        $authUserValue = SecurityClient::getAuthUser();
        $this->assertEquals($authUser, $authUserValue);

        $authUser = 'New Test Value';
        SecurityClient::setAuthUser($authUser);
        $authUserValue = SecurityClient::getAuthUser();
        $this->assertEquals($authUser, $authUserValue);
    }

    /**
     * Test validating the Auth User
     *
     * @return void
     *
     * @covers ::getConfigManager
     * @covers ::validateAuthUser
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getEnvonment
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     */
    public function testValidateAuthUser() : void
    {
        $expectedFalse = SecurityClient::validateAuthUser('');
        $this->assertEquals(false, $expectedFalse);

        $expectedTrue = SecurityClient::validateAuthUser('AUTH_USER');
        $this->assertEquals(true, $expectedTrue);
    }

    /**
     * Sets up Config Manager before a test is ran
     *
     * @return void
     */
    public function setUp() : void
    {
        self::$configManager = new ConfigManager(null, null, null, null, null, true);
        self::$configManager->setValue('logging.api.enabled', false);
        self::$configManager->setValue('testing.authorization.auth_user', 'test');
        self::$configManager->setValue('testing.authorization.access_token', 'test');
    }
}
