<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Listing Video class.
 *
 * @author Andrew Christensen andrew@critical-code.com
 */
class ListingVideo extends Resource {

  /**
   * Delete the listing video.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/deleteListingVideo
   * @return boolean
   */
  public function delete() {
    return $this->deleteRequest(
      "/application/shops/{$this->shop_id}/listings/{$this->listing_id}/videos/{$this->listing_video_id}"
    );
  }
}
