<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Ledger resource class. Represents an Etsy shop's ledger.
 *
 * @link https://www.etsy.com/developers/documentation/reference/ledger
 * @author Rhys Hall hello@rhyshall.com
 */
class Ledger extends Resource {

  /**
   * Get all ledger entries.
   *
   * @param array $params
   * @return \Etsy\Collection
   */
  public function getEntries(array $params = []) {
    return $this->request(
        "GET",
        "/shops/{$this->shop_id}/ledger/entries",
        "LedgerEntry",
        $params
      )
      ->append(['shop_id' => $this->shop_id]);
  }

  /**
   * Get a specific ledger entry.
   *
   * @param integer $entry_id
   * @return \Etsy\Resources\LedgerEntry
   */
  public function getEntry($entry_id) {
    return $this->request(
        "GET",
        "/shops/{$this->shop_id}/ledger/entries/{$entry_id}",
        "LedgerEntry"
      )
      ->append(['shop_id' => $this->shop_id])
      ->first();
  }

}
