<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * AccountEntry resource class. Represents an entry in an Etsy shop's payment ledger.
 *
 * @link https://www.etsy.com/developers/documentation/reference/paymentaccountledgerentry
 * @author Rhys Hall hello@rhyshall.com
 */
class AccountEntry extends Resource {

  /**
   * Get the payment associated with this ledger entry.
   *
   * @return \Etsy\Resources\Payment
   */
  public function getPayment() {
    return $this->request(
        "GET",
        "/shops/{$this->shop_id}/payment_account/entries/{$this->entry_id}/payment",
        "Payment"
      )
      ->first();
  }

}
