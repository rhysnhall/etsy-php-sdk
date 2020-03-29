<?php

namespace Etsy\Resources;

use Etsy\Resource;

class Transaction extends Resource {

  protected $assocations = [
    'Buyer' => 'User',
    'Seller' => 'User',
    'Listing' => 'Listing',
    'Receipt' => 'Receipt',
    'MainImage' => 'ListingImage'
  ];

}
