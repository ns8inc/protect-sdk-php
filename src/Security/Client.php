<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Security;

use NS8\ProtectSDK\Config\Manager as ConfigManager;
use function sprintf;

/**
 * Class for dictating general Security class structure for managing authentication and access to NS8
 */
class Client extends BaseClient
{
    /**
     * Config Manager used to fetch settings
     *
     * @var ConfigManager $configManager
     */
    protected static $configManager;

    /**
     * Returns the Config Manager to be used for making API requests
     *
     * @return ConfigManager the client to be used
     */
    public static function getConfigManager() : ConfigManager
    {
        self::$configManager = self::$configManager ?? new ConfigManager();

        return self::$configManager;
    }

     /**
      * Sets an explicit Config Manager for making API requests
      *
      * @param ConfigManager $configManager The client we are passing in to make requests
      *
      * @return void
      */
    public static function setConfigManager(ConfigManager $configManager) : void
    {
        self::$configManager = $configManager;
    }

    /**
     * Validate NS8 Access Token
     *
     * @param string $accessToken The access token to be validated
     *
     * @return bool Returns true if NS8 Access Token is valid, false otherwise
     */
    public static function validateNs8AccessToken(string $accessToken) : bool
    {
        return ! empty($accessToken);
    }

    /**
     * Returns the NS8 Access Token to be used when sending requests to NS8.
     *
     * @return string The access token to be used when sending NS8 requests
     */
    public static function getNs8AccessToken() : string
    {
        return self::getConfigManager()->getEnvValue('authorization.access_token');
    }

    /**
     * Sets the NS8 Access Token for NS8 requests. This is a wrapper method for the supported config
     * manager call
     *
     * @param string $accessToken The access token to be set in configuration
     *
     * @return void
     */
    public static function setNs8AccessToken(string $accessToken) : void
    {
        $configKey = sprintf('%s.authorization.access_token', ConfigManager::getEnvironment());
        self::getConfigManager()->setValue($configKey, $accessToken);
    }

    /**
     * Validate NS8 Auth User
     *
     * @param string $authUser The auth user value we are validating
     *
     * @return bool Returns true if NS8 Auth User is valid, false otherwise
     */
    public static function validateAuthUser(string $authUser) : bool
    {
        return ! empty($authUser);
    }

    /**
     * Returns the NS8 Auth User to be used when sending requests to NS8.
     *
     * @return string The Auth User to be used when sending NS8 requests
     */
    public static function getAuthUser() : string
    {
        return self::getConfigManager()->getEnvValue('authorization.auth_user');
    }

    /**
     * Sets the Auth User for NS8 requests. This is a wrapper method for the supported config
     * manager call
     *
     * @param string $authUser Auth User to be set in configuration
     *
     * @return void
     */
    public static function setAuthUser(string $authUser) : void
    {
        $configKey = sprintf('%s.authorization.auth_user', ConfigManager::getEnvironment());
        self::getConfigManager()->setValue($configKey, $authUser);
    }
}
