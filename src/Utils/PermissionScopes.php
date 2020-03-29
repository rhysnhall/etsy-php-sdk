<?php

namespace Etsy\Utils;

/**
 * Etsy permission scopes.
 *
 * @author Rhys Hall hello@rhyshall.com
 */
class PermissionScopes {

  const ALL_SCOPES = [
    'email_r', 'listings_r', 'listings_w', 'listings_d', 'transactions_r', 'transactions_w', 'billing_r', 'profile_r', 'profile_w', 'address_r', 'address_w', 'favorites_rw', 'shops_rw', 'cart_rw', 'recommend_rw', 'feedback_r'
  ];

  /**
   * Prepares an array of scopes.
   *
   * @param string|array $scopes
   * @return string
   */
  public static function prepare($scope) {
    if(is_array($scope)) {
      $scope = implode(
        " ",
        array_map("trim", array_filter($scope))
      );
    }
    return $scope;
  }

}
