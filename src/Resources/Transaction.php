<?php

namespace Etsy\Resources;

use Etsy\{Resource, Etsy};

class Transaction extends Resource {

  public function getBuyer() {
    $response = Etsy::makeRequest('GET', "/users/{$this->_properties->buyer_user_id}");
    return Etsy::getResource($response, 'User');
  }

  public function getSeller() {
    $response = Etsy::makeRequest('GET', "/users/{$this->_properties->seller_user_id}");
    return Etsy::getResource($response, 'User');
  }

}
