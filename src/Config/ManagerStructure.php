<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Config;

use NS8\ProtectSDK\Config\Exceptions\Environment as EnvironmentConfigException;
use NS8\ProtectSDK\Config\Exceptions\InvalidValue as InvalidValueException;
use NS8\ProtectSDK\Config\Exceptions\Json as JsonConfigException;
use const JSON_ERROR_NONE;
use function array_merge;
use function dirname;
use function file_exists;
use function file_get_contents;
use function in_array;
use function json_decode;
use function json_last_error;
use function phpversion;
use function sprintf;

/**
 * Abstract class describing how Config Manager classes should be structured
 */
abstract class ManagerStructure
{
    /**
     * Delimiter used in key parsing (e.g. "production.urls.api_url")
     */
    public const KEY_DELIMITER = '.';

    /**
     * Core configuration file name
     */
    public const DEFAULT_CONFIG_FILE = 'core_configuration.json';

    /**
     * Constants related to what defines an environment value as valid
     */
    public const ENV_PRODUCTION               = 'production';
    public const ENV_TESTING                  = 'testing';
    public const ENV_DEVELOPMENT              = 'development';
    public const ACCEPTED_CONFIG_ENVIRONMENTS = [
        self::ENV_PRODUCTION,
        self::ENV_TESTING,
        self::ENV_DEVELOPMENT,
    ];

    /**
     * Production URLs that should remain static in configuration
     */
    public const PRODUCTION_CLIENT_URL_KEY   = self::ENV_PRODUCTION . self::KEY_DELIMITER
        . 'urls' . self::KEY_DELIMITER . 'client_url';
    public const PRODUCTION_API_URL_KEY      = self::ENV_PRODUCTION . self::KEY_DELIMITER
        . 'urls' . self::KEY_DELIMITER . 'api_url';
    public const PRODUCTION_CLEINT_URL_VALUE = 'https://protect-client.ns8.com';
    public const PRODUCTION_API_URL_VALUE    = 'https://protect.ns8.com';

    /**
     * Testing URLs that should remain static in configuration
     */
    public const TESTING_CLIENT_URL_KEY   = self::ENV_TESTING . self::KEY_DELIMITER
        . 'urls' . self::KEY_DELIMITER . 'client_url';
    public const TESTING_API_URL_KEY      = self::ENV_TESTING . self::KEY_DELIMITER
        . 'urls' . self::KEY_DELIMITER . 'api_url';
    public const TESTING_CLIENT_URL_VALUE = 'https://test-protect-client.ns8.com';
    public const TESTING_API_URL_VALUE    = 'https://test-protect.ns8.com';

    /**
     * Mapping of keys/values that should remain static in configuration
     */
    public const STATIC_CONFIG_MAPPINGS = [
        self::PRODUCTION_CLIENT_URL_KEY => self::PRODUCTION_CLEINT_URL_VALUE,
        self::PRODUCTION_API_URL_KEY => self::PRODUCTION_API_URL_VALUE,
        self::TESTING_CLIENT_URL_KEY => self::TESTING_CLIENT_URL_VALUE,
        self::TESTING_API_URL_KEY => self::TESTING_API_URL_VALUE,
    ];

    /**
     * The environment the configuration should utilize during runtime
     *
     * @var string $environment
     */
    protected static $environment;
    /**
     * Attribute to configuration information set during application flow
     *
     * @var mixed[] $configData
     */
    protected static $configData;

    /**
     * Constructor for Configuration manager
     *
     * @param string $environment          The environment the configuration should utilize during runtime
     * @param string $customConfigJsonFile Custom JSON file to be passed into the constructor for configuration set-up
     * @param string $baseConfigJsonFile   Base JSON file to be passed into the construction for configuration set-up
     * @param string $platformVersion      Current version of the platform being utilized
     * @param string $phpVersion           Version of PHP being utilized
     * @param bool   $forceConfigReload    Determines if we should force configuration reloading with the constructor
     */
    public function __construct(
        ?string $environment = null,
        ?string $customConfigJsonFile = null,
        ?string $baseConfigJsonFile = null,
        ?string $platformVersion = null,
        ?string $phpVersion = null,
        bool $forceConfigReload = false
    ) {
        // Ensure the environment passed in is valid
        if (! empty($environment) && ! in_array($environment, self::ACCEPTED_CONFIG_ENVIRONMENTS)) {
            throw new EnvironmentConfigException(sprintf('%s is not a valid environment type.', $environment));
        }

        // Force env and config data reload if explicitly told to do so
        if (! $forceConfigReload) {
            return;
        }

        $baseConfigJsonFile = $baseConfigJsonFile ??
        dirname(__FILE__) . sprintf('/../../assets/configuration/%s', self::DEFAULT_CONFIG_FILE);
        $baseData           = $this->getConfigByFile($baseConfigJsonFile);
        $customData         = isset($customConfigJsonFile) ? $this->getConfigByFile($customConfigJsonFile) : [];

        self::$configData                     = array_merge($baseData, $customData);
        self::$configData['platform_version'] = $platformVersion;
        self::$configData['php_version']      = $phpVersion ?? phpversion();
        self::$environment                    = $environment ?? self::$configData['default_environment'];

        $this->validateInitialConfigData();
    }

    /**
     * Sets a configuration value for a specific key
     *
     * @param string $key   Key for value in configuration array
     * @param mixed  $value Value for the associated key
     *
     * @return bool if the value setting was successful
     */
    abstract public static function setValue(string $key, $value) : bool;

    /**
     * Returns a value from the configuration array given the key.
     * The key can map to a multi-dimensional array via dot parsing (e.g. database.connection.host)
     * to permit granular configuration
     *
     * @param string $key Key for configuration data we want to retrieve
     *
     * @return mixed Return value stored in config for the given key
     */
    abstract public static function getValue(string $key);

    /**
     * Returns a value from the configuration array given the key for the environment
     *
     * @param string $key Key for environmental configuration data we want to retrieve
     *
     * @return mixed Return value stored in config for the given env key
     */
    abstract public static function getEnvValue(string $key);

    /**
     * Deterines if the configuration key exists within the configuration data present
     *
     * @param string $key Key for configuration data we want to check
     *
     * @return bool if the value exists in configuration data
     */
    abstract public static function doesValueExist(string $key) : bool;

    /**
     * Set the environment without object instantiatin for simpler static usage
     *
     * @param string $environment The environment the runtime should utilize for configuration
     *
     * @return void
     */
    public static function setEnvonment(string $environment) : void
    {
        self::$environment = $environment;
    }

    /**
     * Returns the current environment being used by the config manager
     *
     * @return string
     */
    public static function getEnvonment() : string
    {
        return self::$environment;
    }

    /**
     * Returns a configuration array from a file based on the file parth
     *
     * @param string $fileName File path for where the coinfiguration is stored
     *
     * @return mixed[] JSON data decoded
     */
    protected function getConfigByFile(string $fileName) : array
    {
        if (! file_exists($fileName)) {
            throw new JsonConfigException(sprintf('Configuration file %s does not exist.', $fileName));
        }

        return $this->readJsonFromFile($fileName);
    }

    /**
     * Parses a JSON array from configuration file
     *
     * @param string $fileName JSON file to be decoded
     *
     * @return mixed[] JSON data decoded
     *
     * @throws JsonConfigException if the JSON was not decoded without an error.
     */
    protected function readJsonFromFile(string $fileName) : array
    {
        $fileData = file_get_contents($fileName);
        $jsonData = json_decode($fileData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonConfigException(sprintf('%s does not contain valid JSON.', $fileName));
        }

        return $jsonData;
    }

    /**
     * Validates initial configuration values are sane
     *
     * @return void
     */
    protected function validateInitialConfigData() : void
    {
        foreach (self::STATIC_CONFIG_MAPPINGS as $key => $value) {
            if ($this->doesValueExist($key) && $this->getValue($key) !== $value) {
                throw new InvalidValueException(sprintf('%s must have a value of %s', $key, $value));
            }
        }
    }
}
