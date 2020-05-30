<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Transaction resource class. Represents a single item sale on Etsy.
 *
 * @link https://www.etsy.com/developers/documentation/reference/transaction
 * @author Rhys Hall hello@rhyshall.com
 */
class Transaction extends Resource {

  /**
   * @var array
   */
  protected $_associations = [
    'Buyer' => 'User',
    'MainImage' => 'ListingImage',
    'Listing' => 'Listing',
    'Seller' => 'User',
    'Receipt' => 'Receipt'
  ];

}
