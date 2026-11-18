<?php

namespace Etsy\Tests\Resources;

use Etsy\Resources\{Listing, ListingInventory, ShippingProfile};
use Etsy\Tests\TestCase;

class ListingTest extends TestCase {

  private function listings(array $extra = []): array {
    return ['count' => 2, 'results' => [
      ['listing_id' => 11, 'shop_id' => 5] + $extra,
      ['listing_id' => 12, 'shop_id' => 5] + $extra,
    ]];
  }

  public function testGet(): void {
    $this->respond(['listing_id' => 11, 'shop_id' => 5, 'title' => 'Mug']);
    $listing = Listing::get(11);

    $this->assertLastRequest('GET', '/application/listings/11');
    $this->assertInstanceOf(Listing::class, $listing);
    $this->assertSame('Mug', $listing->title);
  }

  public function testIncludedAssociationsAreReturnedWithoutAnotherRequest(): void {
    $this->respond([
      'listing_id' => 11,
      'shop_id' => 5,
      'images' => [['listing_image_id' => 1, 'url_570xN' => 'https://example.com/1.jpg']],
      'videos' => [['video_id' => 2]],
      'personalization' => ['personalization_questions' => []],
    ]);
    $listing = Listing::get(11, ['includes' => 'Images,Videos,Personalization']);
    $requests = $this->client->requestCount();

    $this->assertInstanceOf(\Etsy\Resources\ListingImage::class, $listing->images[0]);
    $this->assertSame('https://example.com/1.jpg', $listing->images[0]->url_570xN);
    $this->assertInstanceOf(\Etsy\Resources\ListingVideo::class, $listing->videos[0]);
    $this->assertInstanceOf(\Etsy\Resources\ListingPersonalization::class, $listing->personalization);
    $this->assertSame($requests, $this->client->requestCount(), 'Included associations should not be fetched again.');
  }

  public function testIncludedPersonalizationCanBeSaved(): void {
    $this->respond(['listing_id' => 11, 'shop_id' => 5, 'personalization' => [
      'personalization_questions' => [['question_text' => 'Name']],
    ]]);
    $personalization = Listing::get(11, ['includes' => 'Personalization'])->personalization;
    $personalization->personalization_questions = [['question_text' => 'Initials']];
    $this->respond(['personalization_questions' => [['question_text' => 'Initials']]]);

    $personalization->save();

    $this->assertLastRequest('POST', '/application/shops/5/listings/11/personalization');
  }

  public function testIncludedPersonalizationInCollectionsGetsIds(): void {
    $this->respond($this->listings(['personalization' => ['personalization_questions' => []]]));
    $personalization = Listing::allByIds([11, 12], ['Personalization'])->data[1]->personalization;

    $this->assertSame(5, $personalization->shop_id);
    $this->assertSame(12, $personalization->listing_id);
  }

  public function testImagesMethodStillFetches(): void {
    $listing = new Listing(json_decode('{"listing_id": 11, "shop_id": 5}'));
    $this->respond(['count' => 0, 'results' => []]);

    $listing->images();
    $this->assertLastRequest('GET', '/application/listings/11/images');
  }

  public function testAllByIds(): void {
    $this->respond($this->listings());
    $listings = Listing::allByIds([11, 12], ['Images']);

    $this->assertLastRequest('GET', '/application/listings/batch');
    $this->assertSame('listing_ids[0]=11&listing_ids[1]=12&includes[0]=Images', $this->lastQuery());
    $this->assertCount(2, $listings->data);
  }

  public function testAllInventoryByIdsSendsCommaSeparatedIds(): void {
    $this->respond($this->listings(['inventory' => ['products' => []]]));
    Listing::allInventoryByIds([11, 12]);

    $this->assertLastRequest('GET', '/application/listings/batch/inventory');
    $this->assertSame('listing_ids=11,12', $this->lastQuery());
  }

  public function testIncludedInventoryIsUsedWithoutAnotherRequest(): void {
    $this->respond($this->listings(['inventory' => [
      'products' => [['product_id' => 7, 'offerings' => [['offering_id' => 70, 'price' => 1]]]],
    ]]));
    $listings = Listing::allInventoryByIds('11,12');
    $requests = $this->client->requestCount();

    $inventory = $listings->data[0]->inventory();

    $this->assertInstanceOf(ListingInventory::class, $inventory);
    $this->assertSame(11, $inventory->listing_id);
    $this->assertSame(11, $inventory->products[0]->listing_id);
    $this->assertSame($requests, $this->client->requestCount(), 'inventory() should not make another request.');
  }

  public function testInventoryIsFetchedWhenNotIncluded(): void {
    $listing = new Listing(json_decode('{"listing_id": 11, "shop_id": 5}'));
    $this->respond(['products' => []]);

    $this->assertInstanceOf(ListingInventory::class, $listing->inventory());
    $this->assertLastRequest('GET', '/application/listings/11/inventory');
  }

  public function testAllShippingByIds(): void {
    $this->respond($this->listings(['shipping_profile' => ['shipping_profile_id' => 77]]));
    $listings = Listing::allShippingByIds([11, 12]);

    $this->assertLastRequest('GET', '/application/listings/batch/shipping');
    $this->assertSame('listing_ids=11,12', $this->lastQuery());
    $profile = $listings->data[0]->shipping_profile;
    $this->assertInstanceOf(ShippingProfile::class, $profile);
    $this->assertSame(77, $profile->shipping_profile_id);
  }

  public function testAllByReturnPolicy(): void {
    $this->respond($this->listings());
    $listings = Listing::allByReturnPolicy(5, 8);

    $this->assertLastRequest('GET', '/application/shops/5/policies/return/8/listings');
    $this->assertCount(2, $listings->data);
  }

  public function testCreateSendsFormData(): void {
    $this->respond(['listing_id' => 99, 'shop_id' => 5], 201);
    Listing::create(5, ['title' => 'Mug', 'quantity' => 1]);

    $this->assertLastRequest('POST', '/application/shops/5/listings');
    $this->assertSame(['title' => 'Mug', 'quantity' => '1'], $this->lastForm());
  }

  public function testSaveSendsGpsrFields(): void {
    $listing = new Listing(json_decode('{"listing_id": 11, "shop_id": 5, "title": "Mug"}'));
    $listing->gpsr_manufacturer_id = 9;
    $listing->gpsr_safety_warnings = 'Hot liquids';
    $this->respond(['listing_id' => 11, 'shop_id' => 5]);

    $listing->save();

    $this->assertLastRequest('PATCH', '/application/shops/5/listings/11');
    $form = $this->lastForm();
    $this->assertSame('9', $form['gpsr_manufacturer_id']);
    $this->assertSame('Hot liquids', $form['gpsr_safety_warnings']);
  }

  public function testDelete(): void {
    $this->client->queue(new \GuzzleHttp\Psr7\Response(204));

    $this->assertTrue(Listing::delete(11));
    $this->assertLastRequest('DELETE', '/application/listings/11');
  }
}
