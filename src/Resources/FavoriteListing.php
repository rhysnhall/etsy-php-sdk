<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * FavoriteListing resource class. Represents a listing favorited by an Etsy user.
 *
 * @link https://www.etsy.com/developers/documentation/reference/favoritelisting
 * @author Rhys Hall hello@rhyshall.com
 */
class FavoriteListing extends Resource {

  /**
   * @var array
   */
  protected $_associations = [
    'Listing' => 'Listing',
    'User' => 'User'
  ];

}
