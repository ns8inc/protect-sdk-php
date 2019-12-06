<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Config;

use Zend\Json\Decoder as ZendJsonDecoder;
use function array_merge;
use function file_exists;
use function file_get_contents;

/**
 * Abstract class describing how Config Manager classes should be structured
 */
abstract class Manager
{
    /**
     * @var mixed[] $configData
     * Attribute to track session data to be sent in requests
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
        $baseData         = [];
        if (isset($baseConfigJsonFile) && file_exists($baseConfigJsonFile)) {
            $baseData = $this->readJsonFromFile($baseConfigJsonFile);
        }

        $customData = [];
        if (isset($customConfigJsonFile) && file_exists($customConfigJsonFile)) {
            $customData = $this->readJsonFromFile($customConfigJsonFile);
        }

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
     *
     * @return mixed
     */
    abstract public function getValue(string $key) : mixed;

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
     * @param string $fileName JSON file to be decoded
     *
     * @return mixed[] JSON data decoded
     */
    protected function readJsonFromFile(string $fileName) : array
    {
        $fileData = file_get_contents($fileName);

        return ZendJsonDecoder($fileData, ZendJsonDecoder::TYPE_ARRAY);
    }
}
