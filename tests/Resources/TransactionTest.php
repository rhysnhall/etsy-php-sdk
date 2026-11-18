<?php

namespace Etsy\Tests\Resources;

use Etsy\Resources\Transaction;
use Etsy\Tests\TestCase;

class TransactionTest extends TestCase {

  public function testAllByReceiptSendsParams(): void {
    $this->respond(['count' => 0, 'results' => []]);
    Transaction::allByReceipt(5, 9, ['legacy' => 'false']);

    $this->assertLastRequest('GET', '/application/shops/5/receipts/9/transactions');
    $this->assertSame('legacy=false', $this->lastQuery());
  }

  public function testAllByListingSendsParams(): void {
    $this->respond(['count' => 0, 'results' => []]);
    Transaction::allByListing(5, 11, ['limit' => 10]);

    $this->assertLastRequest('GET', '/application/shops/5/listings/11/transactions');
    $this->assertSame('limit=10', $this->lastQuery());
  }
}
