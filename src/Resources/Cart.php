<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Cart resource class. Represents an Etsy user's cart.
 * 
 * @link https://www.etsy.com/developers/documentation/reference/cart
 * @author Rhys Hall hello@rhyshall.com
 */
class Cart extends Resource {

  /**
   * @var array
   */
  protected $_associations = [
    'Shop' => 'Shop',
    'Listings' => 'Listing',
    'ShippingOptions' => 'ShippingOption'
  ];

  /**
   * @var array
   */
  protected $_rename = [
    'listings' => 'items'
  ];

  /**
   * Updates the cart.
   *
   * @param array $data
   * @return Cart
   */
  public function update(array $data) {
    return $this->updateRequest(
        "/users/{$this->user_id}/carts/{$this->cart_id}",
        $data
      );
  }

  /**
   * Deletes the cart.
   *
   * @return boolean
   */
  public function delete() {
    return $this->deleteRequest(
        "/users/{$this->user_id}/carts/{$this->cart_id}"
      );
  }

  /**
   * Updates the quantity for an item.
   *
   * @param integer $listing_id
   * @param integer $quantity
   * @param integer $customization_id
   * @return Cart
   */
  public function updateItemQuantity(
    $listing_id,
    $quantity,
    $customization_id = 0
  ) {
    $data = [
      'listing_id' => $listing_id,
      'quantity' => $quantity,
      'listing_customzation_id' => $customization_id
    ];
    return $this->updateRequest(
        "/users/{$this->user_id}/carts",
        $data
      );
  }

  /**
   * Removes an item from the cart.
   *
   * @param integer $listing_id
   * @param integer $customization_id
   * @return Cart
   */
  public function removeItem($listing_id, $customization_id = 0) {
    $data = [
      'listing_id' => $listing_id,
      'listing_customzation_id' => $customization_id
    ];
    return $this->updateRequest(
        "/users/{$this->user_id}/carts",
        $data,
        "DELETE"
      );
  }

  /**
   * Moves an item from the cart to 'Saved for Later'.
   *
   * @param integer $listing_id
   * @param integer $inventory_id
   * @param integer $customization_id
   */
  public function saveForLater(
    $listing_id,
    $inventory_id = 0,
    $customization_id = 0
  ) {
    $data = [
      'cart_id' => $this->cart_id,
      'listing_id' => $listing_id,
      'listing_inventory_id' => $inventory_id,
      'listing_customization_id' => $customization_id
    ];
    $result = $this->deleteRequest(
        "/users/{$this->user_id}/carts/save",
        $data
      );
    // The Save for Later Etsy request does not return an updated cart. As the item has now been removed we need to refresh the cart.
    if($result) {
      $this->update([]);
    }
    return $result;
  }

}
