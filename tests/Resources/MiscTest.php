<?php

namespace Etsy\Tests\Resources;

use Etsy\Exception\SdkException;
use Etsy\Resources\{ShopSection, UserAddress};
use Etsy\Tests\TestCase;
use Etsy\Utils\PermissionScopes;

class MiscTest extends TestCase {

  public function testUserAddressDeleteIsUnsupported(): void {
    $this->expectException(SdkException::class);
    try {
      UserAddress::delete(1);
    }
    finally {
      $this->assertSame(0, $this->client->requestCount(), 'No request should be sent.');
    }
  }

  public function testShopSearchWithoutKeywordThrowsSdkException(): void {
    try {
      \Etsy\Resources\Shop::all('  ');
      $this->fail('Expected an exception.');
    }
    catch(SdkException $e) {
      // Still an ApiException, for code that catches that.
      $this->assertInstanceOf(\Etsy\Exception\ApiException::class, $e);
      $this->assertSame(0, $this->client->requestCount());
    }
  }

  public function testListingPropertyGetFindsThePropertyInTheListing(): void {
    $properties = ['count' => 2, 'results' => [
      ['property_id' => 200, 'property_name' => 'Primary color', 'values' => ['Blue']],
      ['property_id' => 46803063641, 'property_name' => 'Holiday', 'values' => ['Christmas']],
    ]];
    $this->respond($properties);
    $property = \Etsy\Resources\ListingProperty::get(5, 11, 46803063641);

    $this->assertLastRequest('GET', '/application/shops/5/listings/11/properties');
    $this->assertSame('Holiday', $property->property_name);
    $this->assertSame(11, $property->listing_id);

    $this->respond($properties);
    $this->assertNull(\Etsy\Resources\ListingProperty::get(5, 11, 999));
  }

  public function testAllScopesOnlyContainsCurrentScopes(): void {
    $this->assertNotContains('billing_r', PermissionScopes::ALL_SCOPES);
    $this->assertNotContains('feedback_r', PermissionScopes::ALL_SCOPES);
    $this->assertContains('listings_r', PermissionScopes::ALL_SCOPES);
    $this->assertCount(18, PermissionScopes::ALL_SCOPES);
  }

  public function testScopesArePreparedAsSpaceSeparatedString(): void {
    $this->assertSame('listings_r shops_r', PermissionScopes::prepare([' listings_r ', '', 'shops_r']));
    $this->assertSame('listings_r', PermissionScopes::prepare('listings_r'));
  }

  public function testCollectionAppendsShopId(): void {
    $this->respond(['count' => 1, 'results' => [['shop_section_id' => 3, 'title' => 'Mugs']]]);
    $sections = ShopSection::all(5);

    $this->assertLastRequest('GET', '/application/shops/5/sections');
    $this->assertSame(5, $sections->data[0]->shop_id);
  }

  public function testSaveUsesSaveableFieldsAndPut(): void {
    $this->respond(['count' => 1, 'results' => [['shop_section_id' => 3, 'title' => 'Mugs', 'rank' => 1]]]);
    $section = ShopSection::all(5)->data[0];
    $section->title = 'Cups';
    $this->respond(['shop_section_id' => 3, 'title' => 'Cups']);

    $section->save();

    $this->assertLastRequest('PUT', '/application/shops/5/sections/3');
    $this->assertSame(['title' => 'Cups'], $this->lastForm());
  }
}
