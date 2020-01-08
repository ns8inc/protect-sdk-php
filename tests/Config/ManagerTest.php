<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Tests\Config;

use NS8\ProtectSDK\Config\Exceptions\Environment as EnvironmentConfigException;
use NS8\ProtectSDK\Config\Exceptions\InvalidValue as InvalidValueException;
use NS8\ProtectSDK\Config\Exceptions\Json as JsonException;
use NS8\ProtectSDK\Config\Exceptions\ValueNotFound as ValueNotFoundException;
use NS8\ProtectSDK\Config\Manager as ConfigManager;
use PHPUnit\Framework\TestCase;
use function dirname;
use function fclose;
use function fopen;
use function fwrite;
use function json_encode;
use function phpversion;
use function tempnam;
use function unlink;

/**
 * Config Manager Test Class
 *
 * @coversDefaultClass NS8\ProtectSDK\Config\Manager
 */
class ManagerTest extends TestCase
{
    /**
     * Test JSON to use as for a valid JSON config file
     */
    public const TEST_JSON = [
        'logging'   => ['enabled' => true],
        'production'    => [
            'urls' => [
                'client_url' => 'https://protect-client.ns8.com',
                'api_url'   => 'https://protect.ns8.com',
            ],
        ],
        'testing'   => [
            'urls' => [
                'client_url' => 'https://test-protect-client.ns8.com',
                'api_url'   => 'https://test-protect.ns8.com',
            ],
        ],
    ];

    /**
     * Test JSON to use for a valid JSON config file with invalid values
     */
    public const INVALID_URLS_TEST_JSON = [
        'logging'   => ['enabled' => true],
        'production'    => [
            'urls' => [
                'client_url' => 'https://test.ns8.com',
                'api_url'   => 'https://test.ns8.com',
            ],
        ],
        'testing'   => [
            'urls' => [
                'client_url' => 'https://protect-client.ns8.com',
                'api_url'   => 'https://protect.ns8.com',
            ],
        ],
    ];

    /**
     * The test file path with valid JSON for test methods
     *
     * @var string $testFilePath
     */
    protected static $testFilePath = null;

    /**
     * The test file path with valid JSON and invalid values for test methods
     *
     * @var string $testFilePath
     */
    protected static $invalidValuesTestFilePath = null;

    /**
     * The test file path with invalid JSON for test methods
     *
     * @var string $invalidDataTestFilePath
     */
    protected static $invalidDataTestFilePath = null;

    /**
     * Test constructor implementation
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::doesValueExist
     * @covers ::getValue
     * @covers ::validateInitialConfigData
     */
    public function testConstructor() : void
    {
        $this->assertInstanceOf(ConfigManager::class, new ConfigManager());
    }

    /**
     * Test Config Manager with invalid environment
     *
     * @return void
     *
     * @covers ::__construct
     */
    public function testInvalidEnvInitalization() : void
    {
        $this->expectException(EnvironmentConfigException::class);
        $configManager = new ConfigManager('invalid_env', null, self::$testFilePath, null, null, true);
        $configArray   = $configManager::getFullConfigArray();
    }

    /**
     * Test if JSON config loads successfully
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::getConfigByFile
     * @covers ::readJsonFromFile
     * @covers ::getFullConfigArray
     * @covers ::doesValueExist
     * @covers ::getValue
     * @covers ::validateInitialConfigData
     */
    public function testConfigLoad() : void
    {
        $configManager = new ConfigManager('testing', null, self::$testFilePath, null, null, true);
        $configArray   = $configManager::getFullConfigArray();

        foreach (self::TEST_JSON as $key => $value) {
            $this->assertArrayHasKey($key, $configArray);
            $this->assertSame($value, $configArray[$key]);
        }
    }

    /**
     * Test fetching a config value
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::doesValueExist
     * @covers ::validateInitialConfigData
     * @covers ::getConfigByFile
     * @covers ::readJsonFromFile
     * @covers ::getValue
     */
    public function testGetConfigFileValue() : void
    {
        $configManager    = new ConfigManager('testing', null, self::$testFilePath, null, null, true);
        $isLoggingEnabled = $configManager::getValue('logging.enabled');
        $this->assertEquals(self::TEST_JSON['logging']['enabled'], $isLoggingEnabled);
    }

    /**
     * Test fetching an environmental config value
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::doesValueExist
     * @covers ::validateInitialConfigData
     * @covers ::getConfigByFile
     * @covers ::readJsonFromFile
     * @covers ::getValue
     * @covers ::getEnvValue
     */
    public function testGetEnvConfigValue() : void
    {
        $configManager = new ConfigManager('testing', null, self::$testFilePath, null, null, true);
        $apiUrl        = $configManager::getEnvValue('urls.api_url');
        $this->assertEquals(self::TEST_JSON['testing']['urls']['api_url'], $apiUrl);
    }

    /**
     * Test if config value exists validation works
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::doesValueExist
     * @covers ::validateInitialConfigData
     * @covers ::getConfigByFile
     * @covers ::readJsonFromFile
     * @covers ::getValue
     * @covers ::doesValueExist
     */
    public function testValueExistsValidation() : void
    {
        $configManager      = new ConfigManager('testing', null, self::$testFilePath, null, null, true);
        $doesFakeValueExist = $configManager::doesValueExist('this.path.is.not.real');
        $this->assertEquals(false, $doesFakeValueExist);

        $doesRealValueExist = $configManager::doesValueExist('logging.enabled');
        $this->assertEquals(true, $doesRealValueExist);
    }

    /**
     * Test setting a config value dynamically
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::doesValueExist
     * @covers ::validateInitialConfigData
     * @covers ::validateKeyCanChange
     * @covers ::getConfigByFile
     * @covers ::readJsonFromFile
     * @covers ::getValue
     * @covers ::setValue
     */
    public function testSetValue() : void
    {
        $configManager = new ConfigManager('testing', null, self::$testFilePath, null, null, true);

        $configManager->setValue('new.path.value', true);

        $newPathValue = $configManager->getValue('new.path.value');
        $this->assertEquals(true, $newPathValue);
    }

    /**
     * Test Getting a dynamically set config value
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::doesValueExist
     * @covers ::validateInitialConfigData
     * @covers ::getConfigByFile
     * @covers ::readJsonFromFile
     * @covers ::getValue
     * @covers ::setValue
     */
    public function testGetDynamicallySetValue() : void
    {
        $configManager = new ConfigManager('testing', null, self::$testFilePath, null, null, true);

        $this->expectException(ValueNotFoundException::class);
        $configManager->getValue('new.path.value');

        $configManager->setValue('new.path.value', true);

        $newPathValue = $configManager->getValue('new.path.value');
        $this->assertEquals(true, $newPathValue);
    }

    /**
     * Test what occurrs if an invalid JSON file path is set
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::getConfigByFile
     */
    public function testInvalidJsonPath() : void
    {
        $this->expectException(JsonException::class);
        $configManager = new ConfigManager('testing', null, 'invalid_path', null, null, true);
    }

    /**
     * Test what occurrs if JSON is invalid
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::getConfigByFile
     * @covers ::readJsonFromFile
     */
    public function testInvalidJson() : void
    {
        $this->expectException(JsonException::class);
        $configManager = new ConfigManager('testing', null, self::$invalidDataTestFilePath, null, null, true);
    }

    /**
     * Test PHP version functionality used for default values
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::doesValueExist
     * @covers ::validateInitialConfigData
     * @covers ::getValue
     * @covers ::getConfigByFile
     * @covers ::readJsonFromFile
     */
    public function testDefaultPhpVersionCheck() : void
    {
        $configManager = new ConfigManager('testing', null, self::$testFilePath, null, null, true);
        $phpVersion    = $configManager->getValue('php_version');
        $this->assertEquals(phpversion(), $phpVersion);
    }

    /**
     * Test PHP version functionality when passing in a PHP vrersion
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::doesValueExist
     * @covers ::validateInitialConfigData
     * @covers ::getValue
     * @covers ::getConfigByFile
     * @covers ::readJsonFromFile
     */
    public function testPhpVersionIfSpecified() : void
    {
        $testPhpVersion   = 'PHP 7.2.25';
        $configManager    = new ConfigManager('testing', null, self::$testFilePath, null, $testPhpVersion, true);
        $configPhpVersion = $configManager->getValue('php_version');
        $this->assertEquals($testPhpVersion, $configPhpVersion);
    }

     /**
      * Test platform version functionality when no platform version is passed in
      *
      * @return void
      *
      * @covers ::__construct
      * @covers ::doesValueExist
      * @covers ::validateInitialConfigData
      * @covers ::getValue
      * @covers ::getConfigByFile
      * @covers ::readJsonFromFile
      */
    public function testDefaultPlatformVersionCheck() : void
    {
        $configManager = new ConfigManager('testing', null, self::$testFilePath, null, null, true);
        $phpVersion    = $configManager->getValue('platform_version');
        $this->assertEquals(null, $phpVersion);
    }

    /**
     * Test platform version functionality when a platform version is passed in
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::validateInitialConfigData
     * @covers ::doesValueExist
     * @covers ::getValue
     * @covers ::getConfigByFile
     * @covers ::readJsonFromFile
     */
    public function testPlatformVersionIfSpecified() : void
    {
        $testPlatformVersion   = 'Magento 2.3.3';
        $configManager         = new ConfigManager(
            'testing',
            null,
            self::$testFilePath,
            $testPlatformVersion,
            null,
            true
        );
        $configPlatformVersion = $configManager->getValue('platform_version');
        $this->assertEquals($testPlatformVersion, $configPlatformVersion);
    }

    /**
     * Tests set environment functionality
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::doesValueExist
     * @covers ::getValue
     * @covers ::validateInitialConfigData
     * @covers ::getEnvironment
     * @covers ::setEnvironment
     */
    public function testSetEnvironmentMethod() : void
    {
        $configManager = new ConfigManager();
        $configManager->setEnvironment('testing');
        $currentEnv = $configManager::getEnvironment();

        $this->assertEquals('testing', $currentEnv);
    }

    /**
     * Tests get environment functionality
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::doesValueExist
     * @covers ::getValue
     * @covers ::validateInitialConfigData
     * @covers ::getEnvironment
     * @covers ::setEnvironment
     */
    public function testGetEnvironmentMethod() : void
    {
        $configManager = new ConfigManager();
        $currentEnv    = $configManager::getEnvironment();
        $this->assertEquals('testing', $currentEnv);
        $configManager::setEnvironment('production');
        $currentEnv = $configManager::getEnvironment();

        $this->assertEquals('production', $currentEnv);
    }

    /**
     * Test if we are able to override a core URL which we should not be able to
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::doesValueExist
     * @covers ::getValue
     * @covers ::setValue
     * @covers ::validateKeyCanChange
     * @covers ::getConfigByFile
     * @covers ::readJsonFromFile
     * @covers ::validateInitialConfigData
     */
    public function testOverrideEnvUrl() : void
    {
        $configManager = new ConfigManager('testing', null, self::$testFilePath);
        $this->expectException(InvalidValueException::class);
        $configManager->setValue('testing.urls.client_url', 'https://test.com');
    }

    /**
     * Test if we can instantiate a Configuration Manager with invalid env urls
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::doesValueExist
     * @covers ::getValue
     * @covers ::getConfigByFile
     * @covers ::readJsonFromFile
     * @covers ::validateInitialConfigData
     */
    public function testInvalidEnvUrls() : void
    {
        $this->expectException(InvalidValueException::class);
        $configManager = new ConfigManager('testing', null, self::$invalidValuesTestFilePath, null, null, true);
    }

    /**
     * Returns JSON configuration directory for the SDK
     *
     * @return string
     */
    protected static function getJsonConfigDirectoryPath() : string
    {
        return dirname(__FILE__) . '/../../assets/configuration';
    }

    /**
     * Writes test data to a given file path
     *
     * @param string $filePath File path that we want to write data to
     * @param string $testData Data we want to write to a file
     *
     * @return void
     */
    protected static function writeTestData(string $filePath, string $testData) : void
    {
        $fileHandler = fopen($filePath, 'w');
        fwrite($fileHandler, $testData);
        fclose($fileHandler);
    }

    /**
     * Sets up required JSON files for each test ran
     *
     * @return void
     */
    public function setUp() : void
    {
        self::$testFilePath = tempnam(self::getJsonConfigDirectoryPath(), 'php_unit_test_data_');
        self::writeTestData(self::$testFilePath, json_encode(self::TEST_JSON));

        self::$invalidDataTestFilePath = tempnam(self::getJsonConfigDirectoryPath(), 'php_unit_test_data_');
        self::writeTestData(self::$invalidDataTestFilePath, 'Invalid Json');

        self::$invalidValuesTestFilePath = tempnam(self::getJsonConfigDirectoryPath(), 'php_unit_test_data_');
        self::writeTestData(self::$invalidValuesTestFilePath, json_encode(self::INVALID_URLS_TEST_JSON));
    }

    /**
     * Removes JSON files used for testing after tests are ran
     *
     * @return void
     */
    public function tearDown() : void
    {
        unlink(self::$testFilePath);
        unlink(self::$invalidDataTestFilePath);
        unlink(self::$invalidValuesTestFilePath);
    }
}
