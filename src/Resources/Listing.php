<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Listing resource class. Represents an Etsy listing.
 *
 * @link https://developers.etsy.com/documentation/reference#tag/ShopListing
 * @author Rhys Hall hello@rhyshall.com
 */
class Listing extends Resource {

  /**
   * @var array
   */
  protected $_associations = [
    "Shop" => "Shop",
    "User" => "User",
    "Images" => "Image"
  ];

  /**
   * Update the Etsy listing.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/updateListing
   * @param array $data
   * @return Etsy\Resources\Listing
   */
  public function update(array $data) {
    return $this->updateRequest(
      "/application/shops/{$this->shop_id}/listings/{$this->listing_id}",
      $data
    );
  }

  /**
   * Delete the listing.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/deleteListing
   * @return boolean
   */
  public function delete() {
    return $this->deleteRequest(
      "/application/shops/{$this->shop_id}/listings/{$this->listing_id}"
    );
  }

  /**
   * Get the listing properties associated with the listing.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/getListingProperties
   * @return Etsy\Collection[Etsy\Resources\ListingProperty]
   */
  public function getListingProperties() {
    return $this->request(
      "GET",
      "/application/shops/{$this->shop_id}/listings/{$this->listing_id}",
      "ListingProperty"
    )
      ->append([
        'shop_id' => $this->shop_id,
        'listing_id' => $this->listing_id
      ]);
  }

  /**
   * Get a specific listing property.
   *
   * @NOTE This method is not ready for use and will return a 501 repsonse.
   * @link https://developers.etsy.com/documentation/reference#operation/getListingProperty
   * @param integer|string $property_id
   * @return Etsy\Resources\ListingProperty
   */
  public function getListingProperty($property_id) {
    $listing_property = $this->request(
      "GET",
      "/application/listings/{$this->listing_id}/properties/{$property_id}",
      "ListingProperty"
    );
    if($listing_property) {
      $listing_property->shop_id = $this->shop_id;
      $listing_property->listing_id = $this->listing_id;
    }
    return $listing_property;
  }

}
