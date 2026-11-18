<?php

namespace Etsy\Tests\Resources;

use Etsy\Resources\LedgerEntry;
use Etsy\Tests\TestCase;

class LedgerEntryTest extends TestCase {

  public function testAllAssignsShopIdSoPaymentsWork(): void {
    $this->respond(['count' => 1, 'results' => [['entry_id' => 31, 'amount' => 500]]]);
    $entry = LedgerEntry::all(5, ['min_created' => 1, 'max_created' => 2])->first();

    $this->assertSame(5, $entry->shop_id);
    $this->assertArrayNotHasKey(0, $entry->toArray(), 'No junk numeric properties.');

    $this->respond(['count' => 0, 'results' => []]);
    $entry->payments();
    $this->assertLastRequest('GET', '/application/shops/5/payment-account/ledger-entries/payments');
    $this->assertSame('ledger_entry_ids[0]=31', $this->lastQuery());
  }

  public function testAllDefaultsToThePastWeek(): void {
    $this->respond(['count' => 0, 'results' => []]);
    LedgerEntry::all(5);

    parse_str($this->lastRequest()->getUri()->getQuery(), $query);
    $this->assertEqualsWithDelta(7 * 86400, $query['max_created'] - $query['min_created'], 5);
  }
}
