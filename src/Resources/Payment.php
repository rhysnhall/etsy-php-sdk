<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Payment resource class. Represents a payment made with Etsy Payments.
 *
 * @link https://developers.etsy.com/documentation/reference#tag/Payment
 * @author Rhys Hall hello@rhyshall.com
 */
class Payment extends Resource
{
    /**
     * Get payments for a shop.
     *
     * @param int $shop_id
     * @param int|array $payment_ids
     * @return Etsy\Collection[Etsy\Resources\Payment]
     */
    public static function all(
        int $shop_id,
        int|array $payment_ids
    ): \Etsy\Collection {
        return self::request(
            "GET",
            "/application/shops/{$shop_id}/payments",
            "Payment",
            [
            'payment_ids' => $payment_ids
      ]
        );
    }

    /**
     * Get all payments for a specific receipt.
     *
     * @param int $shop_id
     * @param int $receipt_id
     * @return Etsy\Collection[Etsy\Resources\Payment]
     */
    public static function allByReceipt(
        int $shop_id,
        int $receipt_id
    ): \Etsy\Collection {
        return self::request(
            "GET",
            "/application/shops/{$shop_id}/receipts/{$receipt_id}/payments",
            "Payment"
        );
    }

    /**
     * Get payments for one or more ledger entries.
     *
     * @param int $shop_id
     * @param int|array $ledger_entry_ids
     * @return Etsy\Collection[Etsy\Resources\Payment]
     */
    public static function allByLedgerEntries(
        int $shop_id,
        int|array $ledger_entry_ids
    ): \Etsy\Collection {
        return self::request(
            "GET",
            "/application/shops/{$shop_id}/payment-account/ledger-entries/payments",
            "Payment",
            [
            'ledger_entry_ids' => $ledger_entry_ids
      ]
        );
    }

}
