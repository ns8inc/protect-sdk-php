<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Security;

/**
 * Base class for dictating general Security class structure for managing authentication and access to NS8
 */
abstract class BaseClient
{
    /**
     * Validate NS8 Access Token
     *
     * @param string $accessToken The access token to be validated
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
     *
     * @return void
     */
    abstract public static function setNs8AccessToken(string $accessToken) : void;

    /**
     * Validate NS8 Auth User
     *
     * @param ?string $authUser The auth user value we are validating
     *
     * @return bool Returns true if NS8 Auth User is valid, false otherwise
     */
    abstract public static function validateAuthUser(?string $authUser) : bool;

    /**
     * Returns the NS8 Auth User to be used when sending requests to NS8.
     *
     * @return string The Auth User to be used when sending NS8 requests
     */
    abstract public static function getAuthUser() : string;

    /**
     * Sets the Auth User for NS8 requests. This is a wrapper method for the supported config
     * manager call
     *
     * @param string $authUser Auth User to be set in configuration
     *
     * @return void
     */
    abstract public static function setAuthUser(string $authUser) : void;
}
