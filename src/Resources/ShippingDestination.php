<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Shipping Destination class.
 *
 * @link https://developers.etsy.com/documentation/reference#operation/createShopShippingProfileDestination
 * @author Rhys Hall hello@rhyshall.com
 */
class ShippingDestination extends Resource {

  /**
   * Updates the shipping profile.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/updateShopShippingProfileDestination
   * @param array $data
   * @return Etsy\Resources\ShippingDestination
   */
  public function update(array $data) {
    return $this->updateRequest(
      "/application/shops/{$this->shop_id}/shipping-profiles/{$this->shipping_profile_id}/destinations{$this->shipping_profile_destination_id}",
      $data
    );
  }

  /**
   * Delete the shipping profile.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/deleteShopShippingProfileDestination
   * @return boolean
   */
  public function delete() {
    return $this->deleteRequest(
      "/application/shops/{$this->shop_id}/shipping-profiles/{$this->shipping_profile_id}/destinations{$this->shipping_profile_destination_id}",
    );
  }
}
