<?php

namespace Etsy\Tests;

use Etsy\Resources\{Listing, ListingInventory};

/**
 * Behaviour shared by every resource: conversion, change tracking and saving.
 */
class ResourceTest extends TestCase {

  public function testToArrayConvertsIncludedResources(): void {
    $this->respond([
      'listing_id' => 1,
      'price' => ['amount' => 3200, 'divisor' => 100],
      'images' => [['listing_image_id' => 9, 'url_570xN' => 'https://example.com/9.jpg']],
    ]);
    $array = Listing::get(1)->toArray();

    $this->assertSame(['listing_image_id' => 9, 'url_570xN' => 'https://example.com/9.jpg'], $array['images'][0]);
    $this->assertSame(['amount' => 3200, 'divisor' => 100], $array['price']);
  }

  private function saveListingWith(array $original, callable $change): ?array {
    $this->respond(['listing_id' => 1, 'shop_id' => 5] + $original);
    $listing = Listing::get(1);
    $change($listing);
    $requests = $this->client->requestCount();
    $this->respond(['listing_id' => 1, 'shop_id' => 5]);
    $listing->save();
    return $this->client->requestCount() === $requests ? null : $this->lastForm();
  }

  public function testSaveSendsTheWholeChangedArray(): void {
    $sent = $this->saveListingWith(['tags' => ['mug', 'blue', 'clay']], function($l) {
      $l->tags = ['mug', 'blue', 'stoneware'];
    });

    $this->assertSame(['tags' => ['mug', 'blue', 'stoneware']], $sent);
  }

  public function testSaveSendsShrunkAndGrownArrays(): void {
    $this->assertSame(['tags' => ['a']], $this->saveListingWith(['tags' => ['a', 'b']], fn($l) => $l->tags = ['a']));
    $this->assertSame(['tags' => ['a', 'b', 'c']], $this->saveListingWith(['tags' => ['a', 'b']], fn($l) => $l->tags = ['a', 'b', 'c']));
  }

  public function testSaveHandlesArraysThatWereNull(): void {
    $this->assertSame(['tags' => ['new']], $this->saveListingWith(['tags' => null], fn($l) => $l->tags = ['new']));
  }

  public function testSaveSendsNothingWhenArraysAreUnchanged(): void {
    $this->assertNull($this->saveListingWith(['tags' => ['a', 'b'], 'title' => 'Mug'], fn($l) => $l->tags = ['a', 'b']));
  }

  public function testSaveDetectsChangesBetweenFalsyValues(): void {
    $sent = $this->saveListingWith(['is_supply' => null], fn($l) => $l->is_supply = false);

    $this->assertSame(['is_supply' => '0'], $sent);
  }

  public function testToJsonIncludesNestedResources(): void {
    $this->respond(['products' => [[
      'product_id' => 7,
      'sku' => 'MUG',
      'offerings' => [['offering_id' => 70, 'price' => ['amount' => 500]]],
    ]]]);
    $json = json_decode(ListingInventory::get(1)->toJson(), true);

    $this->assertSame('MUG', $json['products'][0]['sku']);
    $this->assertSame(500, $json['products'][0]['offerings'][0]['price']['amount']);
  }
}
