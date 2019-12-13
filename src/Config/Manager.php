<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Config;

use NS8\ProtectSDK\Config\Exceptions\InvalidValue as InvalidValueException;
use NS8\ProtectSDK\Config\Exceptions\ValueNotFound as ValueNotFoundException;
use function array_key_exists;
use function count;
use function explode;
use function is_array;
use function preg_match;
use function sprintf;

/**
 * Configuration manager to keep track of NS8-items
 */
class Manager extends ManagerStructure
{
    /**
     * Rules based on key pattern to prevent overriding specific environment values
     */
    public const SET_VALUE_PREVENTION_RULES =
        ['/^(production|testing)\\' . self::KEY_DELIMITER . 'urls\\' . self::KEY_DELIMITER . '.*/'];

    /**
     * Returns a value from the configuration array given the key.
     * The key can map to a multi-dimensional array via dot parsing (e.g. database.connection.host)
     * to permit granular configuration
     *
     * @param string $key Key for configuration data we want to retrieve
     *
     * @return mixed Return value stored in config for the given key
     */
    public static function getValue(string $key)
    {
        $keyParts    = explode(self::KEY_DELIMITER, $key);
        $keyLength   = count($keyParts);
        $index       = 1;
        $configPath  = self::$configData;
        $returnValue = null;
        foreach ($keyParts as $arrayKey) {
            if (array_key_exists($arrayKey, $configPath)) {
                if ($index === $keyLength) {
                    $returnValue = $configPath[$arrayKey];
                    break;
                }

                $configPath = $configPath[$arrayKey];
                $index++;
                continue;
            }

            throw new ValueNotFoundException(sprintf('%s does not exist as a valid configuration path', $key));
        }

        return $returnValue;
    }

    /**
     * Sets a configuration value for a specific key
     *
     * @param string $key   Key for value in configuration array
     * @param mixed  $value Value for the associated key
     *
     * @return bool if the value setting was successful
     */
    public static function setValue(string $key, $value) : bool
    {
        if (! self::validateKeyCanChange($key)) {
            throw new InvalidValueException(sprintf('%s is not allowed to be changed', $key));
        }

        $keyParts   = explode(self::KEY_DELIMITER, $key);
        $keyLength  = count($keyParts);
        $index      = 1;
        $configPath = &self::$configData;
        foreach ($keyParts as $arrayKey) {
            if ($index === $keyLength) {
                $configPath[$arrayKey] = $value;
                break;
            }

            if (! isset($configPath[$arrayKey]) || ! is_array($configPath[$arrayKey])) {
                $configPath[$arrayKey] = [];
            }

            $configPath = &$configPath[$arrayKey];
            $index++;
        }

        return true;
    }

    /**
     * Validates that a configuration key can be safely changed
     *
     * @param string $key The key that we are valdating if it can be set/modified
     *
     * @return bool True if the key can be changed and false otherwise
     */
    protected static function validateKeyCanChange(string $key) : bool
    {
        foreach (self::SET_VALUE_PREVENTION_RULES as $rule) {
            if (preg_match($rule, $key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * The key can map to a multi-dimensional array via dot parsing (e.g. database.connection.host)
     * to permit granular configuration
     *
     * @param string $key Key for configuration data we want to check
     *
     * @return bool if the value exists in configuration data
     */
    public static function doesValueExist(string $key) : bool
    {
        try {
            $returnValue = true;
            self::getValue($key);
        } catch (ValueNotFoundException $e) {
            $returnValue = false;
        }

        return $returnValue;
    }

    /**
     * Returns a value from the configuration array given the key for the environment
     *
     * @param string $key Key for environmental configuration data we want to retrieve
     *
     * @return mixed Return value stored in config for the given env key
     */
    public static function getEnvValue(string $key)
    {
        $key = self::$environment . self::KEY_DELIMITER . $key;

        return self::getValue($key);
    }

    /**
     * Returns the full configuration array being used to track config values
     *
     * @return mixed[] The current configuration array being used
     */
    public static function getFullConfigArray() : array
    {
        return self::$configData;
    }
}
