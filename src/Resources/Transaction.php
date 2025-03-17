<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Transaction resource class. Represents a single item sale on Etsy.
 *
 * @link https://developers.etsy.com/documentation/reference#tag/Shop-Receipt-Transactions
 * @author Rhys Hall hello@rhyshall.com
 */
class Transaction extends Resource
{
    /**
     * Get all transactions for a shop.
     *
     * @param int $shop_id
     * @param array $params
     * @return Etsy\Collection[Etsy\Resources\Transaction]
     */
    public static function all(
        int $shop_id,
        array $params = []
    ): \Etsy\Collection {
        return self::request(
            "GET",
            "/application/shops/{$shop_id}/transactions",
            "Transaction",
            $params
        );
    }

    /**
     * Get a single transaction.
     *
     * @param int $shop_id
     * @param int $transaction_id
     * @return Etsy\Resources\Transaction
     */
    public static function get(
        int $shop_id,
        int $transaction_id
    ): ?\Etsy\Resources\Transaction {
        return self::request(
            "GET",
            "/application/shops/{$shop_id}/transactions/{$transaction_id}",
            "Transaction"
        );
    }

    /**
     * Get all transactions by listing.
     *
     * @param int $shop_id
     * @param int $listing_id
     * @param array $params
     * @return Etsy\Collection[Etsy\Resources\Transaction]
     */
    public static function allByListing(
        int $shop_id,
        int $listing_id,
        array $params = []
    ): \Etsy\Collection {
        return self::request(
            "GET",
            "/application/shops/{$shop_id}/listings/{$listing_id}/transactions",
            "Transaction",
            $params
        );
    }

    /**
     * Get all transactions by receipt.
     *
     * @param int $shop_id
     * @param int $receipt_id
     * @return Etsy\Collection[Etsy\Resources\Transaction]
     */
    public static function allbyReceipt(
        int $shop_id,
        int $receipt_id
    ): \Etsy\Collection {
        return self::request(
            "GET",
            "/application/shops/{$shop_id}/receipts/{$receipt_id}/transactions",
            "Transaction"
        );
    }
}
