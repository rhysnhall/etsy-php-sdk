<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * ShopSection resource class. Represents a Etsy shop section.
 *
 * @link https://developers.etsy.com/documentation/reference/#tag/Shop-Section
 * @author Rhys Hall hello@rhyshall.com
 */
class ShopSection extends Resource {

  /**
   * Get all listings associated with the shop section.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/getListingsByShopSectionId
   * @param array $params
   * @return Etsy\Collection[Etsy\Resources\Listing]
   */
  public function getListings($params = []) {
    $params['shop_section_ids'] = [$this->shop_section_id];
    return $this->request(
      "GET",
      "/application/shops/{$this->shop_id}/shop-sections/listings",
      "Listing",
      $params
    );
  }

}
