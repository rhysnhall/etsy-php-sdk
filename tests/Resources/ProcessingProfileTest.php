<?php

namespace Etsy\Tests\Resources;

use Etsy\Resources\ProcessingProfile;
use Etsy\Tests\TestCase;

class ProcessingProfileTest extends TestCase {

  private function profile(): ProcessingProfile {
    $this->respond([
      'shop_id' => 5,
      'readiness_state_id' => 3,
      'readiness_state' => 'made_to_order',
      'min_processing_days' => 1,
      'max_processing_days' => 3,
    ]);
    return ProcessingProfile::get(5, 3);
  }

  public function testSaveSendsChangedDaysAsProcessingTime(): void {
    $profile = $this->profile();
    $profile->max_processing_days = 5;
    $this->respond(['shop_id' => 5, 'readiness_state_id' => 3, 'max_processing_days' => 5]);

    $profile->save();

    $this->assertLastRequest('PUT', '/application/shops/5/readiness-state-definitions/3');
    $this->assertSame(['max_processing_time' => '5', 'processing_time_unit' => 'days'], $this->lastForm());
    $this->assertSame(5, $profile->max_processing_days);
  }

  public function testSaveSendsOnlyChangedFields(): void {
    $profile = $this->profile();
    $profile->readiness_state = 'ready_to_ship';
    $this->respond(['shop_id' => 5, 'readiness_state_id' => 3, 'readiness_state' => 'ready_to_ship']);

    $profile->save();

    $this->assertSame(['readiness_state' => 'ready_to_ship'], $this->lastForm());
  }

  public function testSaveWithProcessingTimeAndUnitSetDirectly(): void {
    $profile = $this->profile();
    $profile->max_processing_time = 2;
    $profile->processing_time_unit = 'weeks';
    $this->respond(['shop_id' => 5, 'readiness_state_id' => 3, 'max_processing_days' => 10]);

    $profile->save();

    $this->assertSame(['max_processing_time' => '2', 'processing_time_unit' => 'weeks'], $this->lastForm());
  }

  public function testSaveSendsNothingWhenUnchanged(): void {
    $profile = $this->profile();
    $requests = $this->client->requestCount();

    $profile->save();

    $this->assertSame($requests, $this->client->requestCount());
  }
}
