<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * UserAddress resource class. Represents a User's profile Address in Etsy.
 *
 * @link https://www.etsy.com/developers/documentation/reference/useraddress
 * @author Rhys Hall hello@rhyshall.com
 */
class UserAddress extends Resource {

  /**
   * @var array
   */
  protected $_associations = [
    'Country' => 'Country',
    'User' => 'User'
  ];

  /**
   * Deletes the cart.
   *
   * @return boolean
   */
  public function delete() {
    return $this->deleteRequest(
        "/users/{$this->user_id}/addresses/{$this->user_address_id}"
      );
  }

}
