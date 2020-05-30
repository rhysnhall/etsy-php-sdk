<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Payment resource class. Represents a payment made with Etsy Payments.
 *
 * @link https://www.etsy.com/developers/documentation/reference/payment
 * @author Rhys Hall hello@rhyshall.com
 */
class Payment extends Resource {

  /**
   * Get all refund adjustments for the payment.
   *
   * @param array $params
   * @return \Etsy\Collection
   */
  public function getAdjustments(array $params = []) {
    return $this->request(
        "GET",
        "/payments/{$this->payment_id}/adjustments",
        "PaymentAdjustment",
        $params
      );
  }

  /**
   * Get the receipt this payment belongs to.
   *
   * @param array $includes
   * @return \Etsy\Resources\Receipt
   */
  public function getReceipt(array $includes = []) {
    return $this->request(
        "GET",
        "/receipts/{$this->receipt_id}",
        "Receipt",
        ['includes' => $includes]
      )
      ->append(['shop_id' => $this->shop_id])
      ->first();
  }

}
