<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Shipment resource class. Represents a shipment on a receipt; receipts
 * include these as their `shipments` association.
 *
 * @link https://developers.etsy.com/documentation/reference/#operation/createReceiptShipment
 * @author Rhys Hall hello@rhyshall.com
 */
class Shipment extends Resource {

  /**
   * Create a shipment for a shop receipt.
   *
   * @deprecated Etsy responds with the updated receipt, not a shipment, so the
   *   returned object holds receipt fields. Use Receipt::createShipment() or
   *   $receipt->addTracking() instead.
   * @param int $shop_id
   * @param int $receipt_id
   * @param array $data
   * @return \Etsy\Resources\Shipment
   */
  public static function create(
    int $shop_id,
    int $receipt_id,
    array $data
  ): ?\Etsy\Resources\Shipment {
    return self::request(
      "POST",
      "/application/shops/{$shop_id}/receipts/{$receipt_id}/tracking",
      "Shipment",
      $data,
      ['json' => true]
    );
  }
}
