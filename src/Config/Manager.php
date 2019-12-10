<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Config;

use NS8\ProtectSDK\Config\Exceptions\Json as JsonConfigException;
use const JSON_ERROR_NONE;
use function array_merge;
use function file_exists;
use function file_get_contents;
use function json_decode;
use function json_last_error;
use function sprintf;

/**
 * Abstract class describing how Config Manager classes should be structured
 */
abstract class Manager
{
    /**
     * Attribute to configuration information set during application flow
     *
     * @var mixed[] $configData
     */
    protected $configData;

    /**
     * Constructor for Configuration manager
     *
     * @param string $customConfigJsonFile Custom JSON file to be passed into the constructor for configuration set-up
     * @param string $baseConfigJsonFile   Base JSON file to be passed into the construction for configuration set-up
     */
    public function __construct(?string $customConfigJsonFile = null, ?string $baseConfigJsonFile = null)
    {
        $this->configData = [];
        $baseData         = isset($baseConfigJsonFile) ? $this->getConfigByFile($baseConfigJsonFile) : [];
        $customData       = isset($customConfigJsonFile) ? $this->getConfigByFile($customConfigJsonFile) : [];

        $this->configData = array_merge($baseData, $customData);
    }

    /**
     * Sets a configuration value for a specific key
     *
     * @param string $key   Key for value in configuration array
     * @param mixed  $value Value for the associated key
     *
     * @return Manager
     */
    abstract public function setValue(string $key, mixed $value) : Manager;

    /**
     * Returns a value from the configuration array given the key.
     * The key can map to a multi-dimensional array via dot parsing (e.g. database.connection.host)
     * to permit granular configuration
     *
     * @param string $key Key for configuration data we want to retrieve
     */
    abstract public function getValue(string $key);

    /**
     * Returns if a value exists in the configuration.
     * * The key can map to a multi-dimensional array via dot parsing (e.g. database.connection.host)
     * to permit granular configuration
     *
     * @param string $key Key for configuration data we want to check
     *
     * @return mixed
     */
    abstract public function doesValueExist(string $key) : bool;

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
