<?php

namespace Etsy\Tests\Resources;

use Etsy\Collection;
use Etsy\Resources\{ShippingProfile, ShippingDestination, ShippingUpgrade};
use Etsy\Tests\TestCase;

class ShippingProfileTest extends TestCase {

  private function profile(): ShippingProfile {
    $this->respond([
      'shipping_profile_id' => 4,
      'title' => 'Standard',
      'shipping_profile_destinations' => [['shipping_profile_destination_id' => 40, 'destination_country_iso' => 'US']],
      'shipping_profile_upgrades' => [['upgrade_id' => 41, 'upgrade_name' => 'Express']],
    ]);
    return ShippingProfile::get(5, 4);
  }

  public function testIncludedDestinationsAndUpgradesGetShopId(): void {
    $profile = $this->profile();

    $this->assertInstanceOf(ShippingDestination::class, $profile->shipping_profile_destinations[0]);
    $this->assertSame(5, $profile->shipping_profile_destinations[0]->shop_id);
    $this->assertInstanceOf(ShippingUpgrade::class, $profile->shipping_profile_upgrades[0]);
    $this->assertSame(5, $profile->shipping_profile_upgrades[0]->shop_id);
  }

  public function testDestinationsAndUpgradesDoNotReplaceIncludedData(): void {
    $profile = $this->profile();
    $this->respond(['count' => 1, 'results' => [['shipping_profile_destination_id' => 40]]]);
    $this->respond(['count' => 1, 'results' => [['upgrade_id' => 41]]]);

    $this->assertInstanceOf(Collection::class, $profile->destinations());
    $this->assertInstanceOf(Collection::class, $profile->upgrades());

    $this->assertIsArray($profile->shipping_profile_destinations);
    $this->assertIsArray($profile->shipping_profile_upgrades);
    $json = json_decode($profile->toJson(), true);
    $this->assertSame('US', $json['shipping_profile_destinations'][0]['destination_country_iso']);
    $profile->assignShopIdToProfile(5); // used to throw a TypeError after destinations()
  }
}
