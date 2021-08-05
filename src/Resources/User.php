<?php

namespace Etsy\Resources;

use Etsy\Resource;
use Etsy\Utils\Address as AddressUtil;
use Etsy\Exception\SdkException;

/**
 * User resource class. Represents an Etsy User.
 *
 * @link https://developers.etsy.com/documentation/reference/#tag/User
 * @author Rhys Hall hello@rhyshall.com
 */
class User extends Resource {

  /**
   * Get all addresses for this user.
   *
   * @param array $params
   * @return Etsy\Collection[\Etsy\Resources\UserAddress]
   */
  public function getAddresses(array $params = []) {
    return $this->request(
      "GET",
      "/application/user/addresses",
      "UserAddress",
      $params
    );
  }

  /**
   * Gets a single address for this user.
   *
   * @NOTE this endpoint is not yet active.
   *
   * @param integer/string $address_id
   * @return Etsy\Resources\UserAddress
   */
  public function getAddress($address_id) {
    return $this->request(
      "GET",
      "/application/user/addresses/{$address_id}",
      "UserAddress"
    );
  }

  /**
   * Gets the user's Etsy shop.
   *
   * @return Etsy\Resources\Shop
   */
  public function getShop() {
    return $this->request(
      "GET",
      "/application/users/{$this->user_id}/shops",
      "Shop"
    );
  }

}
