<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Listing Offering class.
 *
 * Etsy no longer exposes a standalone offering endpoint. Offerings are returned
 * as part of a listing product (see ListingProduct and ListingInventory).
 *
 * @link https://developers.etsy.com/documentation/reference#tag/ShopListing-Product
 * @author Rhys Hall hello@rhyshall.com
 */
class ListingOffering extends Resource {

  /**
   * Get a product offering. Fetches the parent listing product and returns the
   * matching offering from it.
   *
   * @param int $listing_id
   * @param int $product_id
   * @param int $offering_id
   * @return \Etsy\Resources\ListingOffering
   */
  public static function get(
    int $listing_id,
    int $product_id,
    int $offering_id
  ): ?\Etsy\Resources\ListingOffering {
    $product = ListingProduct::get($listing_id, $product_id);
    if(!$product) {
      return null;
    }
    return $product->findOffering($offering_id);
  }
}
