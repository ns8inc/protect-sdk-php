<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Security;

/**
 * Get Wrapper for handling general getActions that are requested
 */
abstract class BaseClient
{
    /**
     * Validate NS8 Access Token
     *
     * @param string $accessToken
     *
     * @return bool Returns true if NS8 Access Token is valid, false otherwise
     */
    abstract public static function validateNs8AccessToken(string $accessToken) : bool;

    /**
     * Returns the NS8 Access Token to be used when sending requests to NS8.
     *
     * @return string The access token to be used when sending NS8 requests
     */
    abstract public static function getNs8AccessToken() : string;

    /**
     * Sets the NS8 Access Token for NS8 requests. This is a wrapper method for the supported config
     * manager call
     *
     * @param string $accessToken The access token to be set in configuration
     */
    abstract public static function setNs8AccessToken(string $accessToken) : void;
}
