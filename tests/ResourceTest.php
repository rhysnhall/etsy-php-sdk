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
