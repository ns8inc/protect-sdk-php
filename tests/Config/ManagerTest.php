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
                'api_url'   => 'https://protect.ns8.com',
                'client_url' => 'https://protect-client.ns8.com',
                'js_sdk' => 'https://d3hfiwqcryy9cp.cloudfront.net/assets/js/protect.min.js',
            ],
        ],
        'testing'   => [
            'urls' => [
                'api_url'   => 'https://test-protect.ns8.com',
                'client_url' => 'https://test-protect-client.ns8.com',
                'js_sdk' => 'https://d3hfiwqcryy9cp.cloudfront.net/assets/js/protect.min.js',
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
                'api_url'   => 'https://test.ns8.com',
                'client_url' => 'https://test.ns8.com',
                'js_sdk' => 'https://test.ns8.com',
            ],
        ],
        'testing'   => [
            'urls' => [
                'api_url'   => 'https://protect.ns8.com',
                'client_url' => 'https://protect-client.ns8.com',
                'js_sdk' => 'https://cdn.ns8.com/project.js',
            ],
        ],
    ];

    /**
     * Test JSON to use for a valid JSON config file with invalid values
     */
    public const EMPTY_URLS_TEST_JSON = [
        'logging'   => ['enabled' => true],
        'production'    => [
            'urls' => [
                'api_url'   => 'https://test.ns8.com',
                'client_url' => 'https://test.ns8.com',
                'js_sdk' => 'https://test.ns8.com',
            ],
        ],
        'testing'   => [
            'urls' => [
                'api_url'   => 'https://protect.ns8.com',
                'client_url' => 'https://protect-client.ns8.com',
                'js_sdk' => 'https://cdn.ns8.com/project.js',
            ],
        ],
        'development'   => [
            'urls' => [
                'api_url'   => '',
                'client_url' => '',
                'js_sdk' => 'https://cdn.ns8.com/project.js',
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
     * The test file path with empty development URLs
     *
     * @var string $emptyUrlTestFilePath
     */
    protected static $emptyUrlTestFilePath = null;

    /**
     * Test constructor implementation
     *
     * @return void
     *
     * @covers ::doesValueExist
     * @covers ::getValue
     * @covers ::validateInitialConfigData
     */
    public function testConstructor() : void
    {
        $this->assertInstanceOf(ConfigManager::class, new ConfigManager());
    }

    /**
     * Test if initialization logic works as intended
     *
     * @return void
     *
     * @covers ::getEnvironment
     * @covers ::validateConfigEnvRequirements
     * @covers ::doesValueExist
     * @covers ::getValue
     * @covers ::setRuntimeConfigValues
     * @covers ::setValueWithoutValidation
     * @covers ::getConfigByFile
     * @covers ::initConfiguration
     * @covers ::isConfigInitialized
     * @covers ::readJsonFromFile
     * @covers ::resetConfig
     * @covers ::validateInitialConfigData
     */
    public function testIsConfigInitialized() : void
    {
        ConfigManager::resetConfig();
        $isConfigInitialized = ConfigManager::isConfigInitialized();
        $this->assertEquals(false, $isConfigInitialized);

        ConfigManager::initConfiguration();
        $isConfigInitialized = ConfigManager::isConfigInitialized();
        $this->assertEquals(true, $isConfigInitialized);
    }

    /**
     * Test if JSON config loads successfully
     *
     * @return void
     *
     * @covers ::getEnvironment
     * @covers ::validateConfigEnvRequirements
     * @covers ::getConfigByFile
     * @covers ::readJsonFromFile
     * @covers ::getFullConfigArray
     * @covers ::doesValueExist
     * @covers ::getValue
     * @covers ::validateInitialConfigData
     * @covers ::setRuntimeConfigValues
     * @covers ::setValueWithoutValidation
     * @covers ::initConfiguration
     * @covers ::resetConfig
     */
    public function testConfigLoad() : void
    {
        $configManager = new ConfigManager();
        $configManager->resetConfig();
        $configManager->initConfiguration('testing', null, self::$testFilePath);
        $configArray = $configManager->getFullConfigArray();

        foreach (self::TEST_JSON as $key => $value) {
            $this->assertArrayHasKey($key, $configArray);
            $this->assertSame($value, $configArray[$key]);
        }
    }

    /**
     * Test Config Manager with invalid environment
     *
     * @return void
     *
     * @covers ::initConfiguration
     * @covers ::resetConfig
     */
    public function testInvalidEnvInitalization() : void
    {
        $configManager = new ConfigManager();
        $configManager->resetConfig();
        $this->expectException(EnvironmentConfigException::class);
        $configManager->initConfiguration('invalid_env');
        $configArray = $configManager->getFullConfigArray();
    }

    /**
     * Test fetching a config value
     *
     * @return void
     *
     * @covers ::getEnvironment
     * @covers ::validateConfigEnvRequirements
     * @covers ::doesValueExist
     * @covers ::validateInitialConfigData
     * @covers ::getConfigByFile
     * @covers ::readJsonFromFile
     * @covers ::getValue
     * @covers ::setRuntimeConfigValues
     * @covers ::setValueWithoutValidation
     * @covers ::initConfiguration
     * @covers ::resetConfig
     */
    public function testGetConfigFileValue() : void
    {
        $configManager = new ConfigManager();
        $configManager->resetConfig();
        $configManager->initConfiguration('testing', null, self::$testFilePath);
        $isLoggingEnabled = $configManager->getValue('logging.enabled');
        $this->assertEquals(self::TEST_JSON['logging']['enabled'], $isLoggingEnabled);
    }

    /**
     * Test fetching an environmental config value
     *
     * @return void
     *
     * @covers ::getEnvironment
     * @covers ::validateConfigEnvRequirements
     * @covers ::doesValueExist
     * @covers ::validateInitialConfigData
     * @covers ::getConfigByFile
     * @covers ::readJsonFromFile
     * @covers ::getValue
     * @covers ::getEnvValue
     * @covers ::setRuntimeConfigValues
     * @covers ::setValueWithoutValidation
     * @covers ::initConfiguration
     * @covers ::resetConfig
     */
    public function testGetEnvConfigValue() : void
    {
        $configManager = new ConfigManager();
        $configManager->resetConfig();
        $configManager->initConfiguration('testing', null, self::$testFilePath);
        $apiUrl = $configManager->getEnvValue('urls.api_url');
        $this->assertEquals(self::TEST_JSON['testing']['urls']['api_url'], $apiUrl);
    }

    /**
     * Test if config value exists validation works
     *
     * @return void
     *
     * @covers ::getEnvironment
     * @covers ::validateConfigEnvRequirements
     * @covers ::doesValueExist
     * @covers ::validateInitialConfigData
     * @covers ::getConfigByFile
     * @covers ::readJsonFromFile
     * @covers ::getValue
     * @covers ::doesValueExist
     * @covers ::setRuntimeConfigValues
     * @covers ::setValueWithoutValidation
     * @covers ::initConfiguration
     * @covers ::resetConfig
     */
    public function testValueExistsValidation() : void
    {
        $configManager = new ConfigManager();
        $configManager->resetConfig();
        $configManager->initConfiguration('testing', null, self::$testFilePath);
        $doesFakeValueExist = $configManager->doesValueExist('this.path.is.not.real');
        $this->assertEquals(false, $doesFakeValueExist);

        $doesRealValueExist = $configManager->doesValueExist('logging.enabled');
        $this->assertEquals(true, $doesRealValueExist);
    }

    /**
     * Test setting a config value dynamically
     *
     * @return void
     *
     * @covers ::getEnvironment
     * @covers ::validateConfigEnvRequirements
     * @covers ::doesValueExist
     * @covers ::validateInitialConfigData
     * @covers ::validateKeyCanChange
     * @covers ::getConfigByFile
     * @covers ::readJsonFromFile
     * @covers ::getValue
     * @covers ::setValue
     * @covers ::setRuntimeConfigValues
     * @covers ::setValueWithoutValidation
     * @covers ::initConfiguration
     * @covers ::resetConfig
     */
    public function testSetValue() : void
    {
        $configManager = new ConfigManager();
        $configManager->resetConfig();
        $configManager->initConfiguration('testing', null, self::$testFilePath);

        $configManager->setValue('new.path.value', true);

        $newPathValue = $configManager->getValue('new.path.value');
        $this->assertEquals(true, $newPathValue);
    }

    /**
     * Test Getting a dynamically set config value
     *
     * @return void
     *
     * @covers ::getEnvironment
     * @covers ::validateConfigEnvRequirements
     * @covers ::doesValueExist
     * @covers ::validateInitialConfigData
     * @covers ::getConfigByFile
     * @covers ::readJsonFromFile
     * @covers ::getValue
     * @covers ::setValue
     * @covers ::setRuntimeConfigValues
     * @covers ::setValueWithoutValidation
     * @covers ::initConfiguration
     * @covers ::resetConfig
     */
    public function testGetDynamicallySetValue() : void
    {
        $configManager = new ConfigManager();
        $configManager->resetConfig();
        $configManager->initConfiguration('testing', null, self::$testFilePath);

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
     * @covers ::getConfigByFile
     * @covers ::initConfiguration
     * @covers ::resetConfig
     */
    public function testInvalidJsonPath() : void
    {
        $this->expectException(JsonException::class);
        $configManager = new ConfigManager();
        $configManager->resetConfig();
        $configManager->initConfiguration('testing', null, 'invalid_path');
    }

    /**
     * Test what occurrs if JSON is invalid
     *
     * @return void
     *
     * @covers ::getConfigByFile
     * @covers ::readJsonFromFile
     * @covers ::initConfiguration
     * @covers ::resetConfig
     */
    public function testInvalidJson() : void
    {
        $this->expectException(JsonException::class);
        $configManager = new ConfigManager();
        $configManager->resetConfig();
        $configManager->initConfiguration('testing', null, self::$invalidDataTestFilePath);
    }

    /**
     * Test PHP version functionality used for default values
     *
     * @return void
     *
     * @covers ::getEnvironment
     * @covers ::validateConfigEnvRequirements
     * @covers ::doesValueExist
     * @covers ::validateInitialConfigData
     * @covers ::getValue
     * @covers ::getConfigByFile
     * @covers ::readJsonFromFile
     * @covers ::setRuntimeConfigValues
     * @covers ::setValueWithoutValidation
     * @covers ::initConfiguration
     * @covers ::resetConfig
     */
    public function testDefaultPhpVersionCheck() : void
    {
        $configManager = new ConfigManager();
        $configManager->resetConfig();
        $configManager->initConfiguration('testing', null, self::$testFilePath);

        $phpVersion = $configManager->getValue('php_version');
        $this->assertEquals(phpversion(), $phpVersion);
    }

    /**
     * Test PHP version functionality when passing in a PHP vrersion
     *
     * @return void
     *
     * @covers ::getEnvironment
     * @covers ::validateConfigEnvRequirements
     * @covers ::doesValueExist
     * @covers ::validateInitialConfigData
     * @covers ::getValue
     * @covers ::getConfigByFile
     * @covers ::readJsonFromFile
     * @covers ::setRuntimeConfigValues
     * @covers ::setValueWithoutValidation
     * @covers ::initConfiguration
     * @covers ::resetConfig
     */
    public function testPhpVersionIfSpecified() : void
    {
        $testPhpVersion = 'PHP 7.2.25';
        $configManager  = new ConfigManager();
        $configManager->resetConfig();
        $configManager->initConfiguration('testing', null, self::$testFilePath, null, $testPhpVersion);
        $configPhpVersion = $configManager->getValue('php_version');
        $this->assertEquals($testPhpVersion, $configPhpVersion);
    }

     /**
      * Test platform version functionality when no platform version is passed in
      *
      * @return void
      *
      * @covers ::getEnvironment
      * @covers ::validateConfigEnvRequirements
      * @covers ::doesValueExist
      * @covers ::validateInitialConfigData
      * @covers ::getValue
      * @covers ::getConfigByFile
      * @covers ::readJsonFromFile
      * @covers ::setRuntimeConfigValues
      * @covers ::setValueWithoutValidation
      * @covers ::initConfiguration
      * @covers ::resetConfig
      */
    public function testDefaultPlatformVersionCheck() : void
    {
        $configManager = new ConfigManager();
        $configManager->resetConfig();
        $configManager->initConfiguration('testing', null, self::$testFilePath);
        $phpVersion = $configManager->getValue('platform_version');
        $this->assertEquals(null, $phpVersion);
    }

    /**
     * Test platform version functionality when a platform version is passed in
     *
     * @return void
     *
     * @covers ::getEnvironment
     * @covers ::validateConfigEnvRequirements
     * @covers ::validateInitialConfigData
     * @covers ::doesValueExist
     * @covers ::getValue
     * @covers ::getConfigByFile
     * @covers ::readJsonFromFile
     * @covers ::setRuntimeConfigValues
     * @covers ::setValueWithoutValidation
     * @covers ::initConfiguration
     * @covers ::resetConfig
     */
    public function testPlatformVersionIfSpecified() : void
    {
        $testPlatformVersion = 'Magento 2.3.3';
        $configManager       = new ConfigManager();
        $configManager->resetConfig();
        $configManager->initConfiguration(
            'testing',
            null,
            self::$testFilePath,
            $testPlatformVersion
        );

        $configPlatformVersion = $configManager->getValue('platform_version');
        $this->assertEquals($testPlatformVersion, $configPlatformVersion);
    }

    /**
     * Tests set environment functionality
     *
     * @return void
     *
     * @covers ::doesValueExist
     * @covers ::getValue
     * @covers ::validateInitialConfigData
     * @covers ::getEnvironment
     * @covers ::setEnvironment
     * @covers ::initConfiguration
     * @covers ::resetConfig
     * @covers ::setRuntimeConfigValues
     * @covers ::setValueWithoutValidation
     * @covers ::getConfigByFile
     * @covers ::readJsonFromFile
     */
    public function testSetEnvironmentMethod() : void
    {
        $configManager = new ConfigManager();
        $configManager->resetConfig();
        $configManager->setEnvironment('testing');
        $currentEnv = $configManager->getEnvironment();

        $this->assertEquals('testing', $currentEnv);
    }

    /**
     * Tests get environment functionality
     *
     * @return void
     *
     * @covers ::doesValueExist
     * @covers ::getValue
     * @covers ::validateInitialConfigData
     * @covers ::getEnvironment
     * @covers ::setEnvironment
     * @covers ::initConfiguration
     * @covers ::resetConfig
     * @covers ::setRuntimeConfigValues
     * @covers ::setValueWithoutValidation
     * @covers ::getConfigByFile
     * @covers ::readJsonFromFile
     */
    public function testGetEnvironmentMethod() : void
    {
        $configManager = new ConfigManager();
        $configManager->resetConfig();
        $configManager->setEnvironment('testing');
        $currentEnv = $configManager->getEnvironment();
        $this->assertEquals('testing', $currentEnv);
        $configManager->setEnvironment('production');
        $currentEnv = $configManager->getEnvironment();

        $this->assertEquals('production', $currentEnv);
    }

    /**
     * Test if we are able to override a core URL which we should not be able to
     *
     * @return void
     *
     * @covers ::getEnvironment
     * @covers ::validateConfigEnvRequirements
     * @covers ::doesValueExist
     * @covers ::getValue
     * @covers ::setValue
     * @covers ::validateKeyCanChange
     * @covers ::getConfigByFile
     * @covers ::readJsonFromFile
     * @covers ::validateInitialConfigData
     * @covers ::initConfiguration
     * @covers ::resetConfig
     * @covers ::setRuntimeConfigValues
     * @covers ::setValueWithoutValidation
     * @covers ::getConfigByFile
     * @covers ::readJsonFromFile
     */
    public function testOverrideEnvUrl() : void
    {
        $configManager = new ConfigManager();
        $configManager->resetConfig();
        $configManager->initConfiguration('testing', null, self::$testFilePath);
        $this->expectException(InvalidValueException::class);
        $configManager->setValue('testing.urls.client_url', 'https://test.com');
    }

    /**
     * Test if we can instantiate a Configuration Manager with invalid env urls
     *
     * @return void
     *
     * @covers ::getEnvironment
     * @covers ::validateConfigEnvRequirements
     * @covers ::doesValueExist
     * @covers ::getValue
     * @covers ::getConfigByFile
     * @covers ::readJsonFromFile
     * @covers ::validateInitialConfigData
     * @covers ::setRuntimeConfigValues
     * @covers ::setValueWithoutValidation
     * @covers ::initConfiguration
     * @covers ::resetConfig
     */
    public function testInvalidEnvUrls() : void
    {
        $configManager = new ConfigManager();
        $configManager->resetConfig();
        $this->expectException(InvalidValueException::class);
        $configManager->initConfiguration('testing', null, self::$invalidValuesTestFilePath);
    }

    /**
     * Test if we can instantiate a Configuration Manager with empty env URLs
     * for the development environment
     *
     * @return void
     *
     * @covers ::getEnvironment
     * @covers ::validateConfigEnvRequirements
     * @covers ::doesValueExist
     * @covers ::getValue
     * @covers ::getConfigByFile
     * @covers ::readJsonFromFile
     * @covers ::validateInitialConfigData
     * @covers ::setRuntimeConfigValues
     * @covers ::setValueWithoutValidation
     * @covers ::initConfiguration
     * @covers ::resetConfig
     */
    public function testEmptyEnvUrls() : void
    {
        $configManager = new ConfigManager();
        $configManager->resetConfig();
        $this->expectException(InvalidValueException::class);
        $configManager->initConfiguration('development', null, self::$emptyUrlTestFilePath);
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

        self::$emptyUrlTestFilePath = tempnam(self::getJsonConfigDirectoryPath(), 'php_unit_test_data_');
        self::writeTestData(self::$emptyUrlTestFilePath, json_encode(self::EMPTY_URLS_TEST_JSON));
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
        unlink(self::$emptyUrlTestFilePath);
    }
}
