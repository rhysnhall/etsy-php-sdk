<?php

namespace Etsy\Resources;

use Etsy\{Resource, Etsy};

/**
 * User resource class. Represents an Etsy User.
 *
 * @author Rhys Hall hello@rhyshall.com
 */
class User extends Resource {

  /**
   * Gets the source path for the avatar image.
   *
   * @return string
   */
  public function getAvatarSrc() {
    $response = Etsy::makeRequest(
      "GET",
      "/users/{$this->user_id}/avatar/src"
    );
    return $response->results->src;
  }

  /**
   * Uploads a profile avatar image.
   *
   * @param array $data ['image' => 'path']
   * @return true
   */
  public function uploadAvatar(array $data) {
    $response = Etsy::makeRequest(
      "POST",
      "/users/{$this->user_id}/avatar",
      $data
    );
    return true;
  }

  /**
   * Gets the charge history for the user. The min_created and max_created parameters are required, must be in UNIX time or any string the PHP strtotime method will accept, and cannot be greater than 30 days apart.
   *
   * @param array
   * @return array(\Etsy\Resources\Charge)
   */
  public function getCharges(array $params = []) {
    $response = Etsy::makeRequest(
      "GET",
      "/users/{$this->user_id}/charges",
      $params
    );
    return Etsy::getCollection($response, 'Charge');
  }

  /**
   * Gets the meta data for the users charge history.
   *
   * @return array
   */
  public function getChargesMeta() {
    $response = Etsy::makeRequest(
      "GET",
      "users/{$this->user_id}/charges/meta"
    );
    return $response->results;
  }


}
