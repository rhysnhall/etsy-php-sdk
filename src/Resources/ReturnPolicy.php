<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * ReturnPolicy resource class. Represents a Etsy shop return policy.
 *
 * @link https://developers.etsy.com/documentation/reference/#tag/Shop-Return-Policy
 * @author Andrew Christensen andrew@critical-code.com
 */
class ReturnPolicy extends Resource {

   /**
   * Update the shop return policy.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/updateShopReturnPolicy
   * @param array $data
   * @return Etsy\Resources\ReturnPolicy
   */
  public function update(array $data) {
    return $this->updateRequest(
      "/application/shops/{$this->shop_id}/policies/return/{$this->return_policy_id}",
      $data,
      'PUT'
    );
  }

  /**
   * Delete the return policy.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/deleteShopReturnPolicy
   * @return boolean
   */
  public function delete() {
    return $this->deleteRequest(
      "/application/shops/{$this->shop_id}/policies/return/{$this->return_policy_id}"
    );
  }


   /**
   * Get all listings associated with the shop return policy.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/getListingsByShopReturnPolicy
   * @param array $params
   * @return Etsy\Collection[Etsy\Resources\Listing]
   */
  public function getListings() {
    return $this->request(
        "GET",
        "/application/shops/{$this->shop_id}/policies/return/{$this->return_policy_id}/listings",
        "Listing"
      );
  }

}
