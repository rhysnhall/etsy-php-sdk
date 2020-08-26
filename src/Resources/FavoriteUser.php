<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * FavoriteUser resource class. Represents a user favorited by another Etsy user.
 *
 * @link https://www.etsy.com/developers/documentation/reference/favoriteuser
 * @author Rhys Hall hello@rhyshall.com
 */
class FavoriteUser extends Resource {

  /**
   * @var array
   */
  protected $_associations = [
    'TargetUser' => 'User',
    'User' => 'User'
  ];

}
