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
class LedgerEntry extends Resource
{
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
        // Default period is 7 days. If either is not set, or only one set we override with the default.
        if (!isset($params['min_created']) || !isset($params['max_created'])) {
            $params['min_created'] = Date::now()->modify('-1 week')->getTimestamp();
            $params['max_created'] = Date::now()->getTimestamp();
        }
        if ($params['min_created'] instanceof \DateTime) {
            $params['min_created'] = $params['min_created']->getTimestamp();
        }
        if ($params['max_created'] instanceof \DateTime) {
            $params['max_created'] = $params['max_created']->getTimestamp();
        }
        return self::request(
            "GET",
            "/application/shops/{$shop_id}/payment-account/ledger-entries",
            "LedgerEntry",
            $params
        )->append(['shop_id', $shop_id]);
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
        if ($entry) {
            $entry->shop_id = $shop_id;
        }
        return $entry;
    }

    /**
     * Get payments for the ledger entry.
     *
     * @return \Etsy\Collection[Etsy\Resources\Payment]
     */
    public function payments(): \Etsy\Collection
    {
        return Payment::allByLedgerEntries($this->shop_id, [$this->entry_id]);
    }

}
