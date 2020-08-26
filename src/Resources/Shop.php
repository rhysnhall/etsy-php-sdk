<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Shop resource class. Represents a Etsy user's shop.
 *
 * @link https://www.etsy.com/developers/documentation/reference/shop
 * @author Rhys Hall hello@rhyshall.com
 */
class Shop extends Resource {

  /**
   * @var array
   */
  protected $_associations = [
    'User' => 'User',
    'About' => 'ShopAbout',
    'Sections' => 'ShopSection',
    'Listings' => 'Listing',
    'Receipts' => 'Receipt',
    'Transactions' => 'Transaction',
    'Translations' => 'ShopTranslation',
    'StructuredPolicies' => 'ShopPolicies'
  ];

  /**
   * Get all sections for the shop.
   *
   * @param array $includes
   * @return \Etsy\Collection
   */
  public function getSections(array $includes = []) {
    return $this->request(
        "GET",
        "/shops/{$this->shop_id}/sections",
        "ShopSection",
        ['includes' => $includes]
      )
      ->append(['shop_id' => $this->shop_id]);
  }

  /**
   * Get a specific shop section.
   *
   * @param integer $section_id
   * @param array $includes
   * @return \Etsy\Resources\ShopSection
   */
  public function getSection($section_id, array $includes = []) {
    return $this->request(
        "GET",
        "/shops/{$this->shop_id}/sections/{$section_id}",
        "ShopSection",
        ['includes' => $includes]
      )
      ->append(['shop_id' => $this->shop_id])
      ->first();
  }

  /**
   * Creates a new shop section.
   *
   * @param array $data
   * @return \Etsy\Resources\ShopSection
   */
  public function createSection(array $data) {
    return $this->request(
        "POST",
        "/shops/{$this->shop_id}/sections",
        "ShopSection",
        $data
      )
      ->append(['shop_id' => $this->shop_id])
      ->first();
  }

  /**
   * Get a specific shop receipt.
   *
   * @param int $receipt_id
   * @param array $params
   * @return \Etsy\Resources\Receipt
   */
  public function getReceipt($receipt_id, array $params = []) {
    return $this->request(
        "GET",
        "/receipts/{$receipt_id}",
        "Receipt",
        $params
      )
      ->append(['shop_id' => $this->shop_id])
      ->first();
  }

  /**
   * Get all shop receipts. NOTE: Does not return cancelled orders.
   *
   * @param array $params
   * @return \Etsy\Collection
   */
  public function getReceipts(array $params = []) {
    return $this->request(
        "GET",
        "/shops/{$this->shop_id}/receipts",
        "Receipt",
        $params
      )
      ->append(['shop_id' => $this->shop_id]);
  }

  /**
   * Get all payments account entries.
   *
   * @param array $params
   * @return \Etsy\Collection
   */
  public function getPaymentAccountEntries(array $params = []) {
    return $this->request(
        "GET",
        "/shops/{$this->shop_id}/payment_account/entries",
        "AccountEntry",
        $params
      )
      ->append(['shop_id' => $this->shop_id]);
  }

  /**
   * Get the shop ledger.
   *
   * @return \Etsy\Resources\Ledger
   */
  public function getLedger() {
    return $this->request(
        "GET",
        "/shops/{$this->shop_id}/ledger",
        "Ledger"
      )
      ->first();
  }

  /**
   * Get all item transactions.
   *
   * @param array $params
   * @return \Etsy\Collection
   */
  public function getTransactions(array $params = []) {
    return $this->request(
        "GET",
        "/shops/{$this->shop_id}/transactions",
        "Transaction",
        $params
      );
  }

  /**
   * Get a specific item transaction.
   *
   * @param integer $transaction_id
   * @return \Etsy\Resources\Transaction
   */
  public function getTransaction($transaction_id) {
    return $this->request(
        "GET",
        "/transactions/{$transaction_id}",
        "Transaction"
      )
      ->first();
  }

  /**
   * Gets feedback for the store.
   *
   * @param array $params
   * @return \Etsy\Collection
   */
  public function getFeedback(array $params = []) {
    $params['includes'] = ['Listing'];
    return $this->request(
        "GET",
        "/users/{$this->user_id}/feedback/from-buyers",
        "Feedback",
        $params
      );
  }

}
