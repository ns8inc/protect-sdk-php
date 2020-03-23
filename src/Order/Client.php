<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Order;

use NS8\ProtectSDK\Actions\Client as ActionsClient;
use stdClass;
use function base64_encode;
use function rtrim;
use function sprintf;
use function strtr;

/**
 * Order helper for fetching known, frequently used order attributes.
 */
class Client extends BaseClient
{
    /**
     * Order status values for NS8 Protect Orders
     */
    public const APPROVED_STATE        = 'APPROVED';
    public const MERCHANT_REVIEW_STATE = 'MERCHANT_REVIEW';
    public const CANCELED_STATE        = 'CANCELED';

    /**
     * Get the current merchant.
     *
     * @return stdClass The merchant
     */
    public static function getCurrentMerchant() : stdClass
    {
        return ActionsClient::getEntity('/merchants/current');
    }

    /**
     * Get an order using its name.
     *
     * @param string $name The order name
     *
     * @return stdClass The order
     */
    public static function getOrderByName(string $name) : stdClass
    {
        $uri = sprintf('/orders/order-name/%s', self::base64UrlEncode($name));

        return ActionsClient::getEntity($uri);
    }

    /**
     * Encode a string using base64 in URL mode.
     *
     * @link https://en.wikipedia.org/wiki/Base64#URL_applications
     *
     * @param string $data The data to encode
     *
     * @return string The encoded string
     */
    protected static function base64UrlEncode(string $data) : string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
