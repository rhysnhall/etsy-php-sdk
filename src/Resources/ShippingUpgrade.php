<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Shipping Upgrade class.
 *
 * @link https://developers.etsy.com/documentation/reference#operation/createShopShippingProfileUpgrade
 * @author Rhys Hall hello@rhyshall.com
 */
class ShippingUpgrade extends Resource {

  /**
   * Updates the shipping profile.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/updateShopShippingProfileUpgrade
   * @param array $data
   * @return Etsy\Resources\ShippingUpgrade
   */
  public function update(array $data) {
    return $this->updateRequest(
      "/application/shops/{$this->shop_id}/shipping-profiles/{$this->shipping_profile_id}/upgrades{$this->upgrade_id}",
      $data
    );
  }

  /**
   * Delete the shipping profile.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/deleteShopShippingProfileUpgrade
   * @return boolean
   */
  public function delete() {
    return $this->deleteRequest(
      "/application/shops/{$this->shop_id}/shipping-profiles/{$this->shipping_profile_id}/upgrades{$this->upgrade_id}",
    );
  }
}
