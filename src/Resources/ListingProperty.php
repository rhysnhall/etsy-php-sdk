<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Listing Property class.
 *
 * @link https://developers.etsy.com/documentation/reference#operation/getListingProperties
 * @author Rhys Hall hello@rhyshall.com
 */
class ListingProperty extends Resource {

  /**
   * Updates the listing property.
   *
   * https://developers.etsy.com/documentation/reference#operation/updateListingProperty
   * @param array $data
   * @return Etsy\Resources\ListingProperty
   */
  public function update(array $data) {
    return $this->updateRequest(
      "/application/shops/{$this->shop_id}/listings/{$this->listing_id}/properties/{$this->property_id}",
      $data
    );
  }

  /**
   * Delete the listing property.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/deleteListingProperty
   * @return boolean
   */
  public function delete() {
    return $this->deleteRequest(
      "/application/shops/{$this->shop_id}/listings/{$this->listing_id}/properties/{$this->property_id}"
    );
  }
}
