<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Listing Product class.
 *
 * @link https://developers.etsy.com/documentation/reference#tag/ShopListing-Product
 * @author Rhys Hall hello@rhyshall.com
 */
class ListingProduct extends Resource {

  /**
   * @var array
   */
  protected $_associations = [
    "offerings" => "ListingOffering"
  ];

  /**
   * Get a specific offering from the product.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/getListingOffering
   * @param integer|string $product_offering_id
   * @return Etsy\Resources\ListingOffering
   */
  public function getOffering($product_offering_id) {
    return $this->request(
      "GET",
      "/application/listings/{$this->listing_id}/products/{$this->product_id}/offerings/{$product_offering_id}",
      "ListingOffering"
    );
  }

}
