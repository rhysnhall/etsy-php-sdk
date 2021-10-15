<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Receipt resource class.
 *
 * @link https://developers.etsy.com/documentation/reference#tag/Shop-Receipt
 * @author Rhys Hall hello@rhyshall.com
 */
class Receipt extends Resource {

  /**
   * @var array
   */
  protected $_associations = [
    'shipments' => 'Shipment'
  ];

  /**
   * Creates a new Shipment against the receipt.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/createReceiptShipment
   * @param array $data
   * @return Etsy\Resources\Shipment
   */
  public function createShipment(array $data) {
    $shipment = $this->request(
      "POST",
      "/application/shops/{$this->shop_id}/receipts/{$this->receipt_id}/tracking",
      "Shipment",
      $data
    );
    // Add the shipment to the associated property.
    $this->_properties->shipments[] = $shipment;
    return $shipment;
  }

  /**
   * Gets all transactions for the receipt.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/getShopReceiptTransactionsByReceipt
   * @return Etsy\Collection[Etsy\Resources\Transaction]
   */
  public function getTransactions() {
    return $this->request(
      "GET",
      "/application/shops/{$this->shop_id}/receipts/{$this->receipt_id}/transactions",
      "Transaction"
    );
  }

  /**
   * Gets all payments for the receipt.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/getShopPaymentByReceiptId
   * @return Etsy\Collection[Etsy\Resources\Payment]
   */
  public function getPayments() {
    return $this->request(
      "GET",
      "/application/shops/{$this->shop_id}/receipts/{$this->receipt_id}/payments",
      "Payment"
    );
  }

  /**
   * Get all listings associated with the receipt.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/getListingsByShopReceipt
   * @param array $params
   * @return Etsy\Collection[Etsy\Resources\Listing]
   */
  public function getListings($params = []) {
    return $this->request(
      "GET",
      "/application/shops/{$this->shop_id}/receipts/{$this->receipt_id}/listings",
      "Listing",
      $params
    );
  }

}
