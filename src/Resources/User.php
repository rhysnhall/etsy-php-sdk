<?php

namespace Etsy\Resources;

use Etsy\Etsy;
use Etsy\Resource;
use Etsy\Exception\SdkException;

/**
 * User resource class. Represents an Etsy User.
 *
 * @link https://developers.etsy.com/documentation/reference/#tag/User
 * @author Rhys Hall hello@rhyshall.com
 */
class User extends Resource
{
    /**
     * Get the profiles of the authenticated user or linked buyers.
     *
     * @param ?string|int $user_id
     * @return Etsy\Resources\User
     */
    public static function get(
        string|int $user_id = null
    ): ?\Etsy\Resources\User {
        if (!$user_id) {
            $user_id = self::me()->user_id ?? null;
        }
        return self::request(
            "GET",
            "/application/users/{$user_id}",
            "User"
        );
    }

    /**
     * Get basic info of the user making the request.
     *
     * @return ?array
     */
    public static function me(): ?\stdClass
    {
        $user = Etsy::$client->get("/application/users/me");
        return isset($user->code) && $user->code == 404 ? null : $user;
    }

    /**
     * Get the shop for a specific user.
     *
     * @param int $user_id
     * @return ?Etsy\Resources\Shop
     */
    public static function getShop(
        int $user_id = null
    ): ?\Etsy\Resources\Shop {
        if (!$user_id) {
            $user_id = self::me()->user_id ?? null;
        }
        return self::request(
            "GET",
            "/application/users/{$user_id}/shops",
            "Shop"
        );
    }

    /**
     * Gets the user's Etsy shop.
     *
     * @return Etsy\Resources\Shop
     */
    public function shop(): ?\Etsy\Resources\Shop
    {
        return self::getShop($this->user_id);
    }

}
