<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Templates;

use stdClass;

/**
 * Base class for working with the NS8 Template Service
 */
abstract class BaseClient
{
    /**
     * The template endpoint.
     */
    protected const TEMPLATE_ENDPOINT = '/merchant/template';

    /**
     * The list of valid views that can be provided by the Template Service.
     */
    protected const VALID_VIEWS = [
        'orders-reject',
        'orders-reject-confirm',
        'orders-validate',
        'orders-validate-code',
    ];

    /**
     * Get a template from the NS8 Template Service.
     *
     * @param string        $view           The template name (view) to get
     * @param string        $orderId        The order ID
     * @param string        $token          The access token
     * @param string        $verificationId The customer verification ID
     * @param string        $returnUri      The URI to which the user should be returned (variables get interpolated)
     * @param string[]|null $postParams     Extra POST parameters to send (if null, a GET request will be sent instead)
     *
     * @return stdClass The template
     *
     * @throws RuntimeException If an invalid view is specified.
     */
    abstract public static function get(
        string $view,
        string $orderId,
        string $token,
        string $verificationId,
        string $returnUri,
        ?array $postParams = null
    ) : stdClass;
}
