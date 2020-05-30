<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Receipt resource class. Represents an Etsy Shop_Receipt2.
 *
 * @link https://www.etsy.com/developers/documentation/reference/receipt
 * @author Rhys Hall hello@rhyshall.com
 */
class Receipt extends Resource {

  /**
   * @var array
   */
  protected $_associations = [
    'Country' => 'Country',
    'Buyer' => 'User',
    'GuestBuyer' => 'Guest',
    'Seller' => 'User',
    'Transactions' => 'Transaction',
    'Listings' => 'Listing'
  ];

  /**
   * Get the payment for this receipt.
   *
   * @return \Etsy\Resources\Payment
   */
  public function getPayment() {
    return $this->request(
        "GET",
        "/shops/{$this->shop_id}/receipts/{$this->receipt_id}/payments",
        "Payment"
      )
      ->first();
  }

}
