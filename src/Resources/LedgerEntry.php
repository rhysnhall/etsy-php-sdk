<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * LedgerEntry resource class. Represents an entry in an Etsy shop's ledger.
 *
 * @link https://www.etsy.com/developers/documentation/reference/ledgerentry
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
        "/shops/{$this->shop_id}/ledger/entries/{$this->ledger_entry_id}/payment",
        "Payment"
      )
      ->first();
  }

}
