<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Profile resource class. Represents a users public Etsy profile.
 *
 * @link https://www.etsy.com/developers/documentation/reference/userprofile
 * @author Rhys Hall hello@rhyshall.com
 */
class Profile extends Resource {

  /**
   * @var array
   */
  protected $_associations = [
    'Country' => 'Country'
  ];

  /**
   * Updates the shop section.
   *
   * @param array $data
   * @return Etsy\Resource\Profile
   */
  public function update(array $data) {
    return $this->updateRequest(
        "/users/{$this->user_id}/profile",
        $data
      );
  }

}
