<?php

namespace Etsy\Resources;

use Etsy\Resource;
use Etsy\Resources\{
    Transaction,
    Payment,
    Listing
};

/**
 * Receipt resource class.
 *
 * @link https://developers.etsy.com/documentation/reference#tag/Shop-Receipt
 * @author Rhys Hall hello@rhyshall.com
 */
class Receipt extends Resource
{
    /**
     * @var array
     */
    protected $_associations = [
      'shipments' => 'Shipment'
    ];

    /**
     * @var array
     */
    protected $_saveable = [
      'was_shipped',
      'was_paid'
    ];

    /**
     * Get all receipts for a shop.
     *
     * @param int $shop_id
     * @param array $params
     * @return \Etsy\Collection[\Etsy\Resources\Receipt]
     */
    public static function all(
        int $shop_id,
        array $params = []
    ): \Etsy\Collection {
        return self::request(
            "GET",
            "/application/shops/{$shop_id}/receipts",
            "Receipt",
            $params
        )->append(['shop_id' => $shop_id]);
    }

    /**
     * Get a single receipt.
     *
     * @param int $shop_id
     * @param int $receipt_id
     * @return \Etsy\Resources\Receipt
     */
    public static function get(
        int $shop_id,
        int $receipt_id
    ): ?\Etsy\Resources\Receipt {
        $receipt = self::request(
            "GET",
            "/application/shops/{$shop_id}/receipts/{$receipt_id}",
            "Receipt"
        );
        if ($receipt) {
            $receipt->shop_id = $shop_id;
        }
        return $receipt;
    }

    /**
     * Update a shop receipt.
     *
     * @param int $shop_id
     * @param int $receipt_id
     * @param array $data
     * @return Etsy\Resources\Receipt
     */
    public static function update(
        int $shop_id,
        int $receipt_id,
        array $data
    ): ?\Etsy\Resources\Receipt {
        $receipt = self::request(
            "PUT",
            "/application/shops/{$shop_id}/receipts/{$receipt_id}",
            "Receipt",
            $data
        );
        if ($receipt) {
            $receipt->shop_id = $shop_id;
        }
        return $receipt;
    }

    /**
     * Saves updates to the current section.
     *
     * @param array $data
     * @return Etsy\Resources\Receipt
     */
    public function save(
        ?array $data = null
    ): \Etsy\Resources\Receipt {
        if (!$data) {
            $data = $this->getSaveData();
        }
        if (count($data) == 0) {
            return $this;
        }
        return $this->updateRequest(
            "/application/shops/{$this->shop_id}/receipts/{$this->receipt_id}",
            $data
        );
    }

    /**
     * Get all transactions for the receipt.
     *
     * @return \Etsy\Collection[\Etsy\Resources\Transaction]
     */
    public function transactions(): \Etsy\Collection
    {
        return Transaction::allByReceipt($this->shop_id, $this->receipt_id);
    }

    /**
     * Get all payments for the receipt.
     *
     * @return \Etsy\Collections[\Etsy\Resources\Payment]
     */
    public function payments(): \Etsy\Collection
    {
        return Payment::allByReceipt($this->shop_id, $this->receipt_id);
    }

    /**
     * Get all listings for the receipt.
     *
     * @param array @params
     * @return \Etsy\Collections[\Etsy\Resources\Listing]
     */
    public function listings(
        array $params = []
    ): \Etsy\Collection {
        return Listing::allByReceipt(
            $this->shop_id,
            $this->receipt_id,
            $params
        );
    }

}
