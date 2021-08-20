<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Listing Image class.
 *
 * @link https://developers.etsy.com/documentation/reference#tag/ShopListing-Image
 * @author Rhys Hall hello@rhyshall.com
 */
class ListingImage extends Resource {

  /**
   * Delete the listing image.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/deleteListingImage
   * @return boolean
   */
  public function delete() {
    return $this->deleteRequest(
      "/application/shops/{$this->shop_id}/listings/{$this->listing_id}/images/{$this->listing_image_id}"
    );
  }
}
