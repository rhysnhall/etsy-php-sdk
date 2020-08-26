<?php

namespace Etsy\Resources;

use Etsy\{Resource, Etsy};

/**
 * User resource class. Represents an Etsy User.
 *
 * @link https://www.etsy.com/developers/documentation/reference/user
 * @author Rhys Hall hello@rhyshall.com
 */
class User extends Resource {

  /**
   * Gets the source path for the avatar image.
   *
   * @return string
   */
  public function getAvatarSrc() {
    $response = Etsy::makeRequest(
      "GET",
      "/users/{$this->user_id}/avatar/src"
    );
    return $response->results->src;
  }

  /**
   * Uploads a profile avatar image.
   *
   * @param array $data ['image' => 'path']
   * @return void
   */
  public function uploadAvatar(array $data) {
    $response = Etsy::makeRequest(
      "POST",
      "/users/{$this->user_id}/avatar",
      $data
    );
  }

  /**
   * Gets the charge history for the user. The min_created and max_created parameters are required, must be in UNIX time or any string the PHP strtotime method will accept, and cannot be greater than 30 days apart.
   *
   * @param array $params
   * @return \Etsy\Collection
   */
  public function getCharges(array $params = []) {
    return $this->request(
        "GET",
        "/users/{$this->user_id}/charges",
        "Charge",
        $params
      );
  }

  /**
   * Gets the meta data for the users charge history.
   *
   * @return array
   */
  public function getChargesMeta() {
    $response = Etsy::makeRequest(
      "GET",
      "/users/{$this->user_id}/charges/meta"
    );
    return (array)$response->results;
  }

  /**
   * Gets the user's billing overview.
   *
   * @return \Etsy\Resources\BillingOverview
   */
  public function getBillingOverview() {
    return $this->request(
        "GET",
        "/users/{$this->user_id}/billing/overview",
        "BillingOverview"
      )
      ->first();
  }

  /**
   * Gets the user's bill payments.
   *
   * @param array $params
   * @return \Etsy\Collection
   */
  public function getBillPayments(array $params = []) {
    return $this->request(
        "GET",
        "/users/{$this->user_id}/payments",
        "BillPayment",
        $params
      );
  }

  /**
   * Gets the users carts.
   *
   * @param array $params
   * @return \Etsy\Collection
   */
  public function getCarts(array $params = []) {
    return $this->request(
        "GET",
        "/users/{$this->user_id}/carts",
        "Cart",
        $params
      )
      ->append(['user_id' => $this->user_id]);
  }

  /**
   * Gets a specific cart.
   *
   * @param integer $cart_id
   * @param array $includes
   * @return \Etsy\Resources\Cart
   */
  public function getCart($cart_id, array $includes = []) {
    return $this->request(
        "GET",
        "/users/{$this->user_id}/carts/{$cart_id}",
        "Cart",
        ['includes' => $includes]
      )
      ->append(['user_id' => $this->user_id])
      ->first();
  }

  /**
   * Adds an item to the users cart. No reference to a specific cart is required. Just the listing ID.
   *
   * @param integer $listing_id
   * @param array $data
   * @return \Etsy\Resources\Cart
   */
  public function addToCart($listing_id, array $data = []) {
    $data['listing_id'] = $listing_id;
    return $this->request(
        "POST",
        "/users/{$this->user_id}/carts",
        "Cart",
        $data
      )
      ->append(['user_id' => $this->user_id])
      ->first();
  }

  /**
   * Removes a listing from the users cart.
   *
   * @param integer $listing_id
   * @param integer $customization_id
   * @return \Etsy\Resources\Cart
   */
  public function removeFromCart($listing_id, $customization_id = 0) {
    $data = [
      'listing_id' => $listing_id,
      'listing_customization_id' => $customization_id
    ];
    return $this->request(
        "DELETE",
        "/users/{$this->user_id}/carts",
        "Cart",
        $data
      )
      ->append(['user_id' => $this->user_id])
      ->first();
  }

  /**
   * Get all listings favorited by the user.
   *
   * @param array $params
   * @return \Etsy\Collection
   */
  public function getFavoriteListings(array $params = []) {
    $params['includes'] = ['Listing'];
    return $this->request(
        "GET",
        "/users/{$this->user_id}/favorites/listings",
        "FavoriteListing",
        $params
      );
  }

  /**
   * Finds a favorite listing for the user.
   *
   * @param integer $listing_id
   * @return \Etsy\Resources\FavoriteListing
   */
  public function getFavoriteListing($listing_id) {
    return $this->request(
        "GET",
        "/users/{$this->user_id}/favorites/listings/{$listing_id}",
        "FavoriteListing",
        ['includes' => 'Listing']
      )
      ->first();
  }

  /**
   * Adds a new listing to the users favorited listings.
   *
   * @param integer $listing_id
   * @return \Etsy\Resources\FavoriteListing
   */
  public function addFavoriteListing($listing_id) {
    return $this->request(
        "POST",
        "/users/{$this->user_id}/favorites/listings/{$listing_id}",
        "FavoriteListing",
        ['includes' => 'Listing']
      )
      ->first();
  }

  /**
   * Removes a listing from the users favorited listings.
   *
   * @param integer $listing_id
   * @return boolean
   */
  public function removeFavoriteListing($listing_id) {
    return $this->deleteRequest(
      "/users/{$this->user_id}/favorites/listings/{$listing_id}"
    );
  }

  /**
   * Gets all of the users admirers.
   *
   * @param array $params
   * @return \Etsy\Collection
   */
  public function getAdmirers(array $params = []) {
    $params['includes'] = ['User'];
    return $this->request(
        "GET",
        "/users/{$this->user_id}/favored-by",
        "FavoriteUser",
        $params
      );
  }

  /**
   * Gets all users favorited by this user.
   *
   * @param array $params
   * @return \Etsy\Collection
   */
  public function getFavoriteUsers(array $params = []) {
    $params['includes'] = ['TargetUser'];
    return $this->request(
        "GET",
        "/users/{$this->user_id}/favorites/users",
        "FavoriteUser",
        $params
      );
  }

  /**
   * Gets a favorited user.
   *
   * @param integer/string $target_user_id
   * @return \Etsy\Resources\FavoriteUser
   */
  public function getFavoriteUser($target_user_id) {
    return $this->request(
        "GET",
        "/users/{$this->user_id}/favorites/users/{$target_user_id}",
        "FavoriteUser",
        ['includes' => "TargetUser"]
      )
      ->first();
  }

  /**
   * Favorites a new user.
   *
   * @param integer/string $target_user_id
   * @return \Etsy\Resources\FavoriteUser
   */
  public function addFavoriteUser($target_user_id) {
    return $this->request(
        "POST",
        "/users/{$this->user_id}/favorites/users/{$target_user_id}",
        "FavoriteUser",
        ['includes' => 'TargetUser']
      )
      ->first();
  }

  /**
   * Removes a favorited user.
   *
   * @param integer/string $target_user_id
   * @return boolean
   */
  public function removeFavoriteUser($target_user_id) {
    return $this->deleteRequest(
      "/users/{$this->user_id}/favorites/users/{$target_user_id}"
    );
  }

  /**
   * Gets all feedback authored by the user.
   *
   * @param array $params
   * @return \Etsy\Collection
   */
  public function getAuthoredFeedback(array $params = []) {
    $params['includes'] = ['Listing'];
    return $this->request(
        "GET",
        "/users/{$this->user_id}/feedback/as-author",
        "Feedback",
        $params
      );
  }


}
