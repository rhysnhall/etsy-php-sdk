<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Listing Translation class.
 *
 * @link https://developers.etsy.com/documentation/reference#tag/ShopListing-Translation
 * @author Rhys Hall hello@rhyshall.com
 */
class ListingTranslation extends Resource {

  /**
   * Updates the listing translation.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/updateListingTranslation
   * @param array $data
   * @return Etsy\Resources\ListingTranslation
   */
  public function update(array $data) {
    return $this->updateRequest(
      "/application/shops/{$this->shop_id}/listings/{$this->listing_id}/translations/{$this->language}",
      $data
    );
  }

}
