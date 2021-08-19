<?php

namespace Etsy\Resources;

use Etsy\Resource;
use Etsy\Exception\ApiException;

/**
 * Shop resource class. Represents a Etsy user's shop.
 *
 * @link https://developers.etsy.com/documentation/reference/#tag/Shop
 * @author Rhys Hall hello@rhyshall.com
 */
class Shop extends Resource {

  /**
   * Update the shop.
   *
   * @param array $data
   * @return Etsy\Resources\Shop
   */
  public function update(array $data) {
    return $this->updateRequest(
      "/application/shops/{$this->shop_id}",
      $data
    );
  }

  /**
   * Get all sections for the shop.
   *
   * @param array $params
   * @return \Etsy\Collection
   */
  public function getSections($params = []) {
    return $this->request(
      "GET",
      "/application/shops/{$this->shop_id}/sections",
      "ShopSection",
      $params
    );
  }

  /**
   * Get a specific shop section.
   *
   * @param integer|string $section_id
   * @return \Etsy\Resources\ShopSection
   */
  public function getSection($section_id) {
    return $this->request(
      "GET",
      "/application/shops/{$this->shop_id}/sections/{$section_id}",
      "ShopSection"
    );
  }

  /**
   * Creates a new shop section.
   *
   * @param string $data
   * @return \Etsy\Resources\ShopSection
   */
  public function createSection(string $title) {
    if(!strlen(trim($title))) {
      throw new ApiException("Section title cannot be blank.");
    }
    return $this->request(
      "POST",
      "/application/shops/{$this->shop_id}/sections",
      "ShopSection",
      ["title" => $title]
    );
  }

  /**
   * Get all reviews for the shop.
   *
   * @param array $params
   * @return Etsy\Collection[Etsy\Resources\Review]
   */
  public function getReviews(array $params = []) {
    return $this->request(
      "GET",
      "/application/shops/{$this->shop_id}/reviews",
      "Review",
      $params
    );
  }

  /**
   * Get all shipping profiles for the shop.
   *
   * @return Etsy\Collection[Etsy\Resources\ShippingProfile]
   */
  public function getShippingProfiles() {
    $profiles = $this->request(
      "GET",
      "/application/shops/{$this->shop_id}/shipping-profiles",
      "ShippingProfile"
    )
      ->append(['shop_id' => $this->shop_id]);
    // Assign the shop ID to associated resources.
    array_map(
      (function($profile){
        $this->assignShopIdToProfile($profile);
      }),
      $profiles->data
    );
    return $profiles;
  }

  /**
   * Gets a single shipping profile for the shop.
   *
   * @param integer|string $shipping_profile_id
   * @return Etsy\Resources\ShippingProfile
   */
  public function getShippingProfile($shipping_profile_id) {
    $profile = $this->request(
      "GET",
      "/application/shops/{$this->shop_id}/shipping-profiles/{$shipping_profile_id}",
      "ShippingProfile"
    );
    // Assign the shop id to the profile and associated resources.
    $this->assignShopIdToProfile($profile);
    return $profile;
  }

  /**
   * Creates a new shipping profile for the shop.
   *
   * @link https://developers.etsy.com/documentation/reference/#operation/createShopShippingProfile
   * @param array $data
   * @return Etsy\Resources\ShippingProfile
   */
  public function createShippingProfile(array $data) {
    $profile = $this->request(
      "POST",
      "/application/shops/{$this->shop_id}/shipping-profiles",
      "ShippingProfile",
      $data
    );
    // Assign the shop id to the profile and associated resources.
    $this->assignShopIdToProfile($profile);
    return $profile;
  }

  /**
   * Assigns the shop ID to a shipping profile.
   *
   * @param Etsy\Resources\ShippingProfile $profile
   * @return void
   */
  private function assignShopIdToProfile(
    \Etsy\Resources\ShippingProfile $profile
  ) {
    $profile->shop_id = $this->shop_id;
    array_map(
      (function($destination) {
        $destination->shop_id = $this->shop_id;
      }),
      ($profile->shipping_profile_destinations ?? [])
    );
    array_map(
      (function($upgrade) {
        $upgrade->shop_id = $this->shop_id;
      }),
      ($profile->shipping_profile_upgrades ?? [])
    );
  }

  /**
   * Get all receipts for the shop.
   *
   * @param array $params
   * @return Etsy\Collection[Etsy\Resources\Receipt]
   */
  public function getReceipts(array $params = []) {
    return $this->request(
      "GET",
      "/application/shops/{$this->shop_id}/receipts",
      "Receipt",
      $params
    )
      ->append(['shop_id' => $this->shop_id]);
  }

  /**
   * Gets a single receipt for the shop.
   *
   * @param integer|string $receipt_id
   * @return Etsy\Resources\Receipt
   */
  public function getReceipt($receipt_id) {
    $receipt = $this->request(
      "GET",
      "/application/shops/{$this->shop_id}/receipts/{$receipt_id}",
      "Receipt"
    );
    if($receipt) {
      $receipt->shop_id = $this->shop_id;
    }
    return $receipt;
  }

  /**
   * Get all transactions for the shop.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/getShopReceiptTransactionsByShop
   * @param array $params
   * @return Etsy\Collection[Etsy\Resources\Transaction]
   */
  public function getTransactions(array $params = []) {
    return $this->request(
      "GET",
      "/application/shops/{$this->shop_id}/transactions",
      "Transaction",
      $params
    );
  }

  /**
   * Get a specific transaction for the shop.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/getShopReceiptTransaction
   * @param integer|string $transaction_id
   * @return Etsy\Resources\Transaction
   */
  public function getTransaction($transaction_id) {
    return $this->request(
      "GET",
      "/application/shops/{$this->shop_id}/transactions/{$transaction_id}",
      "Transaction"
    );
  }

  /**
   * Get all payment account ledger entries for the shop.
   *
   * @link https://developers.etsy.com/documentation/reference#tag/Ledger-Entry
   * @param array $params
   * @return Etsy\Collection[Etsy\Resources\LedgerEntry]
   */
  public function getLedgerEntries(array $params = []) {
    return $this->request(
      "GET",
      "/application/shops/{$this->shop_id}/payment-account/ledger-entries",
      "LedgerEntry",
      $params
    )
      ->append(['shop_id' => $this->shop_id]);
  }

  /**
   * Get the specified payments for the shop.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/getPayments
   * @param array $payment_ids
   * @return Etsy\Collection[Etsy\Resources\Payment]
   */
  public function getPayments(array $payment_ids = []) {
    return $this->request(
      "GET",
      "/application/shops/{$this->shop_id}/payments",
      "Payment",
      ["payment_ids" => $payment_ids]
    );
  }

  /**
   * Creates a draft Etsy listing.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/createDraftListing
   * @param array $data
   * @return Etsy\Resources\Listing
   */
  public function createListing(array $data) {
    $listing = $this->request(
      "POST",
      "/application/shops/{$this->shop_id}/listings",
      "Listing",
      $data
    );
    return $listing;
  }

  /**
   * Get the listings for the shop.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/getListingsByShop
   * @param array $params
   * @return Etsy\Collection[Etsy\Resources\Listing]
   */
  public function getListings(array $params = []) {
    $listings = $this->request(
      "GET",
      "/application/shops/{$this->shop_id}/listings",
      "Listing",
      $params
    );
    return $listings;
  }

}
