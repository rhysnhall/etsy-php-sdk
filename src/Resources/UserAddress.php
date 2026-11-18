<?php

namespace Etsy\Resources;

use Etsy\Resource;
use Etsy\Exception\SdkException;

/**
 * UserAddress resource class. Represents a User's profile Address in Etsy. You can only get addresses for the currently authenticated user.
 *
 * @link https://developers.etsy.com/documentation/reference/#tag/UserAddress
 * @author Rhys Hall hello@rhyshall.com
 */
class UserAddress extends Resource {

  /**
   * Get all addresses for a user.
   * 
   * @param array @params
   * @return Etsy\Collection[\Etsy\Resources\UserAddress]
   */
  public static function all(
    array $params = []
  ): \Etsy\Collection {
    return self::request(
      "GET",
      "/application/user/addresses",
      "UserAddress",
      $params
    );
  }

  /**
   * Get a single address for a user.
   * 
   * @param int $address_id
   * @return Etsy\Resources\UserAddress
   */
  public static function get(
    int $address_id
  ): ?\Etsy\Resources\UserAddress {
    return self::request(
      "GET",
      "/application/user/addresses/{$address_id}",
      "UserAddress"
    );
  }

  /**
   * Delete a user address.
   *
   * @deprecated Etsy has removed the deleteUserAddress endpoint from Open API v3.
   * @param int $address_id
   * @return bool
   * @throws \Etsy\Exception\SdkException
   */
  public static function delete(
    int $address_id
  ): bool {
    throw new SdkException(
      "Deleting user addresses is no longer supported by the Etsy API."
    );
  }
}
