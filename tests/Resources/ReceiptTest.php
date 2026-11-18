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

  public function testUpdate(): void {
    $this->respond(['receipt_id' => 9, 'is_shipped' => true]);
    Receipt::update(5, 9, ['was_shipped' => true]);

    $this->assertLastRequest('PUT', '/application/shops/5/receipts/9');
    $this->assertSame(['was_shipped' => '1'], $this->lastForm());
  }
}
