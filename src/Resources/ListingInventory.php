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

}
