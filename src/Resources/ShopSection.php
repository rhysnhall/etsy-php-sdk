<?php

namespace Etsy\Resources;

use Etsy\{Resource, Etsy};

class ShopSection extends Resource {

  /**
   * @var array
   */
  protected $_assocations = [
    'Shop' => 'Shop'
  ];

  public function update($data) {
    $data['includes'] = 'Shop';
    $response = Etsy::makeRequest(
      'PUT',
      "shops/{$this->shop->shop_id}/sections/{$this->shop_section_id}",
      $data
    );
    return Etsy::getResource($response, 'ShopSection');
  }

  public function delete() {
    $response = Etsy::makeRequest(
      'DELETE',
      "shops/{$this->shop->shop_id}/sections/{$this->shop_section_id}"
    );
    return Etsy::getResource($response, 'ShopSection');
  }

}
