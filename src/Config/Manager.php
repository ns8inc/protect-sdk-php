<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Config;

use NS8\ProtectSDK\Config\Exceptions\Environment as EnvironmentConfigException;
use NS8\ProtectSDK\Config\Exceptions\Json as JsonConfigException;
use const JSON_ERROR_NONE;
use function array_merge;
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
abstract class Manager
{
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
     */
    public function __construct(
        string $environment = self::ENV_PRODUCTION,
        ?string $customConfigJsonFile = null,
        ?string $baseConfigJsonFile = null,
        ?string $platformVersion = null,
        ?string $phpVersion = null
    ) {
        if (! in_array($environment, self::ACCEPTED_CONFIG_ENVIRONMENTS)) {
            throw new EnvironmentConfigException(sprintf('%s is not a valid environment type.', $environment));
        }

        $baseData   = isset($baseConfigJsonFile) ? $this->getConfigByFile($baseConfigJsonFile) : [];
        $customData = isset($customConfigJsonFile) ? $this->getConfigByFile($customConfigJsonFile) : [];

        self::$environment                    = $environment;
        self::$configData                     = array_merge($baseData, $customData);
        self::$configData['platform_version'] = $platformVersion;
        self::$configData['php_version']      = $phpVersion ?? phpversion();
    }

    /**
     * Sets a configuration value for a specific key
     *
     * @param string $key   Key for value in configuration array
     * @param mixed  $value Value for the associated key
     *
     * @return Manager
     */
    abstract public static function setValue(string $key, $value) : Manager;

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
     * Returns a value from the configuration array given the key for the environment.
     * The key can map to a multi-dimensional array via dot parsing (e.g. database.connection.host)
     * to permit granular configuration
     *
     * @param string $key Key for environmental configuration data we want to retrieve
     *
     * @return mixed Return value stored in config for the given key
     */
    abstract public static function getEnvValue(string $key);

    /**
     * Returns if a value exists in the configuration.
     * * The key can map to a multi-dimensional array via dot parsing (e.g. database.connection.host)
     * to permit granular configuration
     *
     * @param string $key Key for configuration data we want to check
     *
     * @return mixed
     */
    abstract public static function doesValueExist(string $key) : bool;

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

        return $this->readJsonFromFile($filename);
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
}
