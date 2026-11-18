<?php

namespace Etsy\Tests\Resources;

use Etsy\Resources\HolidayPreference;
use Etsy\Tests\TestCase;

class HolidayPreferenceTest extends TestCase {

  private array $preference = ['shop_id' => 5, 'holiday_id' => 2, 'country_iso' => 'US', 'is_working' => true, 'holiday_name' => 'Thanksgiving'];

  public function testAllHandlesBareListResponse(): void {
    // Etsy returns a JSON array here, not {count, results}.
    $this->respond([$this->preference]);
    $preferences = HolidayPreference::all(5);

    $this->assertLastRequest('GET', '/application/shops/5/holiday-preferences');
    $this->assertInstanceOf(HolidayPreference::class, $preferences->data[0]);
    $this->assertSame('Thanksgiving', $preferences->data[0]->holiday_name);
    $this->assertSame(1, $preferences->count);
  }

  public function testAllHandlesEmptyList(): void {
    $this->respond([]);

    $this->assertSame([], HolidayPreference::all(5)->data);
  }

  public function testSave(): void {
    $this->respond([$this->preference]);
    $preference = HolidayPreference::all(5)->first();
    $preference->is_working = false;
    $this->respond(['is_working' => false] + $this->preference);

    $preference->save();

    $this->assertLastRequest('PUT', '/application/shops/5/holiday-preferences/2');
    $this->assertSame('0', $this->lastForm()['is_working']);
  }
}
