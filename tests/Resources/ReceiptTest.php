<?php

namespace Etsy\Tests\Resources;

use Etsy\Resources\Receipt;
use Etsy\Tests\TestCase;

class ReceiptTest extends TestCase {

  private function receipt(): Receipt {
    $this->respond(['receipt_id' => 9, 'is_shipped' => false, 'is_paid' => true, 'name' => 'A Buyer']);
    return Receipt::get(5, 9);
  }

  public function testSaveSendsIsShippedAsWasShipped(): void {
    $receipt = $this->receipt();
    $receipt->is_shipped = true;
    $this->respond(['receipt_id' => 9, 'is_shipped' => true, 'is_paid' => true]);

    $receipt->save();

    $this->assertLastRequest('PUT', '/application/shops/5/receipts/9');
    $this->assertSame(['was_shipped' => '1'], $this->lastForm());
    $this->assertTrue($receipt->is_shipped);
  }

  public function testSaveSendsNothingWhenUnchanged(): void {
    $receipt = $this->receipt();
    $requests = $this->client->requestCount();

    $receipt->save();

    $this->assertSame($requests, $this->client->requestCount());
  }

  public function testSettingWasFieldsDirectlyStillWorks(): void {
    $receipt = $this->receipt();
    $receipt->was_paid = false;
    $this->respond(['receipt_id' => 9, 'is_shipped' => false, 'is_paid' => false]);

    $receipt->save();

    $this->assertSame(['was_paid' => '0'], $this->lastForm());
  }

  public function testTransactionsUsesIncludedData(): void {
    $this->respond(['receipt_id' => 9, 'transactions' => [
      ['transaction_id' => 1, 'title' => 'Mug'],
      ['transaction_id' => 2, 'title' => 'Bowl'],
    ]]);
    $receipt = Receipt::get(5, 9);
    $requests = $this->client->requestCount();

    $transactions = $receipt->transactions;

    $this->assertSame($requests, $this->client->requestCount(), 'No extra request.');
    $this->assertInstanceOf(\Etsy\Collection::class, $transactions);
    $this->assertInstanceOf(\Etsy\Resources\Transaction::class, $transactions->data[1]);
    $this->assertSame('Bowl', $transactions->data[1]->title);
    $this->assertSame(5, $transactions->data[1]->shop_id);
    $this->assertSame(2, $receipt->transactions()->count());
  }

  public function testTransactionsFetchesWhenNotIncluded(): void {
    $receipt = $this->receipt();
    $this->respond(['count' => 0, 'results' => []]);

    $receipt->transactions();

    $this->assertLastRequest('GET', '/application/shops/5/receipts/9/transactions');
  }

  public function testCreateShipmentReturnsTheReceipt(): void {
    $this->respond(['receipt_id' => 9, 'is_shipped' => true, 'shipments' => [['receipt_shipping_id' => 4, 'tracking_code' => 'TRACK1']]]);
    $receipt = Receipt::createShipment(5, 9, ['tracking_code' => 'TRACK1', 'carrier_name' => 'usps']);

    $this->assertLastRequest('POST', '/application/shops/5/receipts/9/tracking');
    $this->assertSame(['tracking_code' => 'TRACK1', 'carrier_name' => 'usps'], $this->lastJson());
    $this->assertInstanceOf(Receipt::class, $receipt);
    $this->assertSame(5, $receipt->shop_id);
    $this->assertSame('TRACK1', $receipt->shipments[0]->tracking_code);
  }

  public function testAddTrackingUpdatesTheReceipt(): void {
    $receipt = $this->receipt();
    $this->respond(['receipt_id' => 9, 'is_shipped' => true, 'shipments' => [['tracking_code' => 'TRACK1']]]);

    $result = $receipt->addTracking(['tracking_code' => 'TRACK1', 'carrier_name' => 'usps']);

    $this->assertLastRequest('POST', '/application/shops/5/receipts/9/tracking');
    $this->assertSame(['tracking_code' => 'TRACK1', 'carrier_name' => 'usps'], $this->lastJson());
    $this->assertSame($receipt, $result);
    $this->assertTrue($receipt->is_shipped);
  }

  public function testUpdate(): void {
    $this->respond(['receipt_id' => 9, 'is_shipped' => true]);
    Receipt::update(5, 9, ['was_shipped' => true]);

    $this->assertLastRequest('PUT', '/application/shops/5/receipts/9');
    $this->assertSame(['was_shipped' => '1'], $this->lastForm());
  }
}
