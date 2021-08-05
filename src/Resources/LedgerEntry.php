<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * LedgerEntry resource class. Represents an entry in an Etsy shop's ledger.
 *
 * @link https://developers.etsy.com/documentation/reference#tag/Ledger-Entry
 * @author Rhys Hall hello@rhyshall.com
 */
class LedgerEntry extends Resource {

  /**
   * Get the payment associated with this ledger entry.
   *
   * @return \Etsy\Resources\Payment
   */
  public function getPayment() {
    return $this->request(
      "GET",
      "/application/shops/{$this->shop_id}/payment-account/ledger-entries/payments",
      "Payment",
      ["ledger_entry_ids" => [$this->entry_id]]
    )
      ->first();
  }

}
