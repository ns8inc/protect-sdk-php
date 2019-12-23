<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Actions;

/**
 * Get Wrapper for handling general getActions that are requested
 */
abstract class BaseClient
{
    /**
     * Known events for the NS8 Platform
     */
    public const ON_INSTALL_PLATFORM_EVENT                 = 'ON_INSTALL_PLATFORM_EVENT';
    public const UPDATE_CUSTOMER_VERIFICATION_STATUS_EVENT = 'UPDATE_CUSTOMER_VERIFICATION_STATUS_EVENT';
    public const UPDATE_EQ8_SCORE_EVENT                    = 'UPDATE_EQ8_SCORE_EVENT';
    public const UPDATE_ORDER_RISK_EVENT                   = 'UPDATE_ORDER_RISK_EVENT';
    public const UPDATE_ORDER_STATUS_EVENT                 = 'UPDATE_ORDER_STATUS_EVENT';
    public const ON_ENABLE_EXTENSION_EVENT                 = 'ON_ENABLE_EXTENSION_EVENT';
    public const ON_UPDATE_EXTENSION_EVENT                 = 'ON_UPDATE_EXTENSION_EVENT';
    public const ON_DISABLE_EXTENSION_EVENT                = 'ON_DISABLE_EXTENSION_EVENT';
    public const ORDER_READY_EVENT                         = 'ORDER_READY_EVENT';
    public const SESSION_DECORATED_EVENT                   = 'SESSION_DECORATED_EVENT';
    public const SESSION_SCORED_EVENT                      = 'SESSION_SCORED_EVENT';
    public const PAYMENT_DECORATED_EVENT                   = 'PAYMENT_DECORATED_EVENT';
    public const PAYMENT_SCORED_EVENT                      = 'PAYMENT_SCORED_EVENT';
    public const DEFAULT_FLOW_COMPLETED_EVENT              = 'DEFAULT_FLOW_COMPLETED_EVENT';

    /**
     * Known actions for ther NS8 Platform
     */
    public const CREATE_ORDER_ACTION        = 'CREATE_ORDER_ACTION';
    public const UNINSTALL_ACTION           = 'UNINSTALL_ACTION';
    public const UPDATE_ORDER_STATUS_ACTION = 'UPDATE_ORDER_STATUS_ACTION';
    public const UPDATE_MERCHANT_ACTION     = 'UPDATE_MERCHANT_ACTION';
    public const WEBHOOK_ACTION             = 'WEBHOOK_ACTION';



    /**
     * A list of predefined NS8 events to serve enumeration/validation purposes
     *
     * @var mixed[] $predefinedEvents
     */
    protected $predefinedEvents = [
        self::ON_INSTALL_PLATFORM_EVENT,
        self::UPDATE_CUSTOMER_VERIFICATION_STATUS_EVENT,
        self::UPDATE_EQ8_SCORE_EVENT,
        self::UPDATE_ORDER_RISK_EVENT,
        self::UPDATE_ORDER_STATUS_EVENT,
        self::ON_ENABLE_EXTENSION_EVENT,
        self::ON_UPDATE_EXTENSION_EVENT,
        self::ON_DISABLE_EXTENSION_EVENT,
        self::ORDER_READY_EVENT,
        self::SESSION_DECORATED_EVENT,
        self::SESSION_SCORED_EVENT,
        self::PAYMENT_DECORATED_EVENT,
        self::PAYMENT_SCORED_EVENT,
        self::DEFAULT_FLOW_COMPLETED_EVENT,
    ];

    /**
     * A list of predefined NS8 actions to serve enumeration/validation purposes
     *
     * @var mixed[] $predefinedActions
     */
    protected $predefinedActions = [
        self::CREATE_ORDER_ACTION,
        self::UNINSTALL_ACTION,
        self::UPDATE_ORDER_STATUS_ACTION,
        self::UPDATE_MERCHANT_ACTION,
        self::WEBHOOK_ACTION,
    ];

    /**
     * Get function that serves as a wrapper method for HTTP GET calls to NS8 for fetching such info as an Order Score
     *
     * @param string  $requestType The type of info we are intending to fetch
     * @param mixed[] $data        The data needed for retrieving the requestion information
     *
     * @return mixed Returns the result of the NS8 API call
     */
    abstract public static function getEntity(string $requestType, array $data = []);

    /**
     * Set function that serves as a wrapper method for HTTP POST calls to NS8 when Actrions
     * are triggered on the client side.
     *
     * @param string  $eventName The event that has occurred to send data to the NS8 API
     * @param mixed[] $data      Data related to the event that has occurred
     *
     * @return bool if the NS8 API set call was completed successfully (true if successful, false otherwise)
     */
    abstract public static function setAction(string $eventName, array $data = []) : bool;
}
