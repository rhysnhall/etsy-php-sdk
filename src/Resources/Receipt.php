<?php

namespace Etsy\Resources;

use Etsy\Resource;
use Etsy\Etsy;
use Etsy\Collection;
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
class Receipt extends Resource {

  /**
   * @var array
   */
  protected $_associations = [
    'shipments' => 'Shipment'
  ];

  /**
   * Receipts have is_shipped and is_paid, but Etsy's update endpoint takes
   * them as was_shipped and was_paid. save() maps between the two. Setting
   * was_shipped or was_paid directly also works.
   *
   * @var array
   */
  protected $_saveable = [
    'is_shipped',
    'is_paid',
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
    return self::requestCollection(
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
    if($receipt) {
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
    if($receipt) {
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
    if(!$data) {
      // Send only what changed, using the names the update endpoint expects.
      $data = [];
      foreach($this->getSaveData(true) as $field => $value) {
        $data[str_replace('is_', 'was_', $field)] = $value;
      }
    }
    if(count($data) == 0) {
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
  public function transactions(): \Etsy\Collection {
    // Receipts usually include their transactions. Use them rather than
    // making another request.
    $included = $this->_properties->transactions ?? null;
    if(is_array($included)) {
      $collection = new Collection('Transaction');
      $collection->data = Etsy::createCollectionResources($included, 'Transaction');
      $collection->count = count($collection->data);
      $collection->next_page = false;
      return $collection->append(['shop_id' => $this->shop_id]);
    }
    return Transaction::allByReceipt($this->shop_id, $this->receipt_id);
  }

  /**
   * Submit tracking information for a receipt, creating a shipment. Etsy
   * emails the buyer each time this succeeds.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/createReceiptShipment
   * @param int $shop_id
   * @param int $receipt_id
   * @param array $data e.g. tracking_code, carrier_name, send_bcc, note_to_buyer
   * @return \Etsy\Resources\Receipt The updated receipt.
   */
  public static function createShipment(
    int $shop_id,
    int $receipt_id,
    array $data
  ): ?\Etsy\Resources\Receipt {
    $receipt = self::request(
      "POST",
      "/application/shops/{$shop_id}/receipts/{$receipt_id}/tracking",
      "Receipt",
      $data,
      ['json' => true]
    );
    if($receipt) {
      $receipt->shop_id = $shop_id;
    }
    return $receipt;
  }

  /**
   * Submit tracking information for this receipt and update it with Etsy's
   * response. Etsy emails the buyer each time this succeeds.
   *
   * @param array $data e.g. tracking_code, carrier_name, send_bcc, note_to_buyer
   * @return \Etsy\Resources\Receipt
   */
  public function addTracking(
    array $data
  ): \Etsy\Resources\Receipt {
    return $this->updateRequest(
      "/application/shops/{$this->shop_id}/receipts/{$this->receipt_id}/tracking",
      $data,
      "POST",
      ['json' => true]
    );
  }

  /**
   * Get all payments for the receipt.
   * 
   * @return \Etsy\Collections[\Etsy\Resources\Payment]
   */
  public function payments(): \Etsy\Collection {
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
