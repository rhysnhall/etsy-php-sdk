<?php

namespace Etsy\Resources;

use Etsy\{Resource, Etsy};

class Shop extends Resource {

  public function getAllShopSections() {
    $response = Etsy::makeRequest('GET', "shops/{$this->shop_id}/sections");
    return Etsy::getCollection($response, 'ShopSection');
  }

  public function getShopSection($shop_section_id) {
    $response = Etsy::makeRequest(
      'GET',
      "shops/{$this->shop_id}/sections/{$shop_section_id}",
      [
        'includes' => 'Shop'
      ]
    );
    return Etsy::getResource($response, 'ShopSection');
  }

  public function createShopSection($data) {
    $response = Etsy::makeRequest('POST', "shops/{$this->shop_id}/sections", $data);
    return Etsy::getResource($response, 'ShopSection');
  }

}
