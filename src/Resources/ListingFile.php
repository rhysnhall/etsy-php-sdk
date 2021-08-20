<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Listing File class.
 *
 * @link https://developers.etsy.com/documentation/reference#tag/ShopListing-File
 * @author Rhys Hall hello@rhyshall.com
 */
class ListingFile extends Resource {

  /**
   * Delete the listing file.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/deleteListingFile
   * @return boolean
   */
  public function delete() {
    return $this->deleteRequest(
      "/application/shops/{$this->shop_id}/listings/{$this->listing_id}/files/{$this->listing_file_id}"
    );
  }
}
