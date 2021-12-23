<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Listing Inventory class.
 *
 * @link https://developers.etsy.com/documentation/reference#tag/ShopListing-Inventory
 * @author Rhys Hall hello@rhyshall.com
 */
class ListingInventory extends Resource {

  /**
   * @var array
   */
  protected $_associations = [
    "products" => "ListingProduct"
  ];

  /**
   * Update the Listing Inventory record.
   *
   * @link https://developers.etsy.com/documentation/reference/#operation/updateListingInventory
   * @param array $data
   * @return Etsy\Resources\ListingInventory
   */
  public function update() {
    // Don't use a regular resource update request here.
    $inventory = $this->updateRequest(
      "/application/listings/{$this->listing_id}/inventory",
      $data
    );
    // Assign the listing ID to associated inventory products.
    array_map(
      (function($product){
        $product->listing_id = $this->listing_id;
      }),
      ($inventory->products ?? [])
    );
    return $this;
  }

}
