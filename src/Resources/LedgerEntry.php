<?php

namespace Etsy\Resources;

use Etsy\Resource;
use Etsy\Utils\Date;
use Etsy\Resources\Payment;

/**
 * LedgerEntry resource class. Represents an entry in an Etsy shop's ledger.
 *
 * @link https://developers.etsy.com/documentation/reference#tag/Ledger-Entry
 * @author Rhys Hall hello@rhyshall.com
 */
class LedgerEntry extends Resource {

  /**
   * Get all ledger entries for a shop.
   * 
   * @param int $shop_id
   * @param array $params
   * @return Etsy\Collection[Etsy\Resources\LedgerEntry]
   */
  public static function all(
    int $shop_id,
    array $params = []
  ): \Etsy\Collection {
    // Dates can be timestamps or any DateTimeInterface. max_created defaults
    // to now, and min_created to a week before max_created.
    foreach(['min_created', 'max_created'] as $key) {
      if(($params[$key] ?? null) instanceof \DateTimeInterface) {
        $params[$key] = $params[$key]->getTimestamp();
      }
    }
    $params['max_created'] = $params['max_created'] ?? Date::now()->getTimestamp();
    $params['min_created'] = $params['min_created'] ?? (int)$params['max_created'] - 7 * 86400;
    return self::requestCollection(
      "GET",
      "/application/shops/{$shop_id}/payment-account/ledger-entries",
      "LedgerEntry",
      $params
    )->append(['shop_id' => $shop_id]);
  }

  /**
   * Get a single ledger entry.
   * 
   * @param int $shop_id
   * @param int $ledger_entry_id
   * @return Etsy\Resources\LedgerEntry
   */
  public static function get(
    int $shop_id,
    int $ledger_entry_id
  ): ?\Etsy\Resources\LedgerEntry {
    $entry = self::request(
      "GET",
      "/application/shops/{$shop_id}/payment-account/ledger-entries/{$ledger_entry_id}",
      "LedgerEntry"
    );
    if($entry) {
      $entry->shop_id = $shop_id;
    }
    return $entry;
  }

  /**
   * Get payments for the ledger entry.
   * 
   * @return \Etsy\Collection[Etsy\Resources\Payment]
   */
  public function payments(): \Etsy\Collection {
    return Payment::allByLedgerEntries($this->shop_id, [$this->entry_id]);
  }

}
