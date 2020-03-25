<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Installer;

use NS8\ProtectSDK\Http\Client as HttpClient;
use NS8\ProtectSDK\Installer\Exceptions\MissingData as MissingDataException;
use function array_key_exists;
use function sprintf;

/**
 * Client for uninstalling the NS8 Protect module
 */
class Client extends BaseClient
{
    /**
     * Platform Installation endpoint
     */
    public const INSTALL_ENDPOINT = '/platform/install/%s';
    /**
     * Array keys required for NS8 Protect installation
     */
    public const EMAIL_KEY        = 'email';
    public const FIRST_NAME_KEY   = 'firstName';
    public const LAST_NAME_KEY    = 'lastName';
    public const PHONE_NUMBER_KEY = 'phone';
    public const STORE_URL_KEY    = 'storeUrl';

    /**
     * Error messages to display for missing installation data
     */
    public const EMAIL_MISSING_EXCEPTION_MESSAGE       =
    'Array key "email" is missing: A valid email is required';
    public const FIRST_NAME_MISSING_EXCEPTION_MESSAGE  =
    'Array key "firstName" is missing: A first name value is required';
    public const LAST_NAME_MISSING_EXCEPTION_MESSAGE   =
    'Array key "lastName" is missing: A last name value is required';
    public const PHONE_NUMER_MISSING_EXCEPTION_MESSAGE =
    'Array key "phone" is missing: A phone number is required';
    public const STORE_URL_MISSING_EXCEPTION_MESSAGE   =
    'Array key "storeUrl" is missing: A valid store URL (e.g. https://example.com) is required';

    /**
     * Mapping of required keys to their error message for missing installation information
     */
    public const REQUIRED_ARRAY_KEY_VALIDATION_MAPPING = [
        self::EMAIL_KEY => self::EMAIL_MISSING_EXCEPTION_MESSAGE,
        self::STORE_URL_KEY => self::STORE_URL_MISSING_EXCEPTION_MESSAGE,
    ];

    /**
     * HTTP Client used to make API requests
     *
     * @var HttpClient
     */
    protected static $httpClient;

    /**
     * Install the NS8 Protect module.
     *
     * @param string  $platformName The platform we are utilizing (e.g. Magento)
     * @param mixed[] $installData  The data related to the merchant install
     *
     * @return mixed[] The response containing accessToken information
     */
    public static function install(string $platformName, array $installData) : array
    {
        self::validateInstallDataArray($installData);
        $installEndpoint = sprintf(self::INSTALL_ENDPOINT, $platformName);

        return (array) self::getHttpClient()->executeJsonRequest($installEndpoint, $installData);
    }

    /**
     * Returns the HTTP client to be used for making API requests
     *
     * @return HttpClient the client to be used
     */
    public static function getHttpClient() : HttpClient
    {
        self::$httpClient = self::$httpClient ?? new HttpClient();

        return self::$httpClient;
    }

    /**
     * Sets an explicit HTTP client for making API requests
     *
     * @param HttpClient $httpClient The client we are passing in to make requests
     *
     * @return void
     */
    public static function setHttpClient(HttpClient $httpClient) : void
    {
        self::$httpClient = $httpClient;
    }

    /**
     * Performs basic validation of the installation data array
     *
     * @param mixed[] $installData Array data that we want to validate
     *
     * @return void
     */
    protected static function validateInstallDataArray(array $installData) : void
    {
        foreach (self::REQUIRED_ARRAY_KEY_VALIDATION_MAPPING as $key => $message) {
            if (! array_key_exists($key, $installData)) {
                throw new MissingDataException($message);
            }
        }
    }
}
