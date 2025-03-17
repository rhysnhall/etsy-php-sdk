<?php

namespace Etsy\Utils;

/**
 * Etsy permission scopes.
 *
 * @author Rhys Hall hello@rhyshall.com
 */
class PermissionScopes
{
    public const ALL_SCOPES = [
      "address_r", "address_w", "billing_r", "cart_r", "cart_w", "email_r", "favorites_r", "favorites_w", "feedback_r", "listings_d", "listings_r", "listings_w", "profile_r", "profile_w", "recommend_r", "recommend_w", "shops_r", "shops_w", "transactions_r", "transactions_w"
    ];

    /**
     * Prepares an array of scopes.
     *
     * @param string|array $scopes
     * @return string
     */
    public static function prepare($scope)
    {
        if (is_array($scope)) {
            $scope = implode(
                " ",
                array_map("trim", array_filter($scope))
            );
        }
        return $scope;
    }

}
