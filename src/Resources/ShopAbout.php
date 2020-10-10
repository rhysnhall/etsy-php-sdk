<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * ShopAbout resource class. Represents the about section of an Etsy shop.
 *
 * @link https://www.etsy.com/developers/documentation/reference/shopabout
 * @author Rhys Hall hello@rhyshall.com
 */
class ShopAbout extends Resource {

  /**
   * @var array
   */
  protected $_associations = [
    'Shop' => 'Shop'
  ];


}
