<?php

namespace Etsy\Tests;

use Etsy\Exception\SdkException;
use Etsy\Resources\{Listing, Review, Shop, ShopSection};

class CollectionTest extends TestCase {

  private function page(int $from, int $count): array {
    $results = [];
    for($i = 0; $i < $count; $i++) {
      $results[] = ['listing_id' => $from + $i, 'shop_id' => 5];
    }
    return ['count' => 7, 'results' => $results];
  }

  public function testPaginateFetchesFurtherPagesUsingOffset(): void {
    $this->respond($this->page(1, 3));
    $listings = Listing::allActiveByShop(5, ['limit' => 3]);
    $this->respond($this->page(4, 3));
    $this->respond($this->page(7, 1));

    $ids = [];
    foreach($listings->paginate(100) as $listing) {
      $ids[] = $listing->listing_id;
    }

    $this->assertSame([1, 2, 3, 4, 5, 6, 7], $ids);
    $this->assertSame('limit=3&offset=6', $this->lastQuery());
    $this->assertSame(3, $this->client->requestCount(), 'Should stop after a short page.');
  }

  public function testPaginateStopsAtRequestedResults(): void {
    $this->respond($this->page(1, 3));
    $listings = Listing::allActiveByShop(5, ['limit' => 3]);
    $this->respond($this->page(4, 3));

    $this->assertCount(6, iterator_to_array($listings->paginate(5), false));
    $this->assertSame(2, $this->client->requestCount());
  }

  public function testPaginateStopsWhenAPageFails(): void {
    $this->respond($this->page(1, 3));
    $listings = Listing::allActiveByShop(5, ['limit' => 3]);
    $this->respond(['error' => 'Not found'], 404);

    $this->assertCount(3, iterator_to_array($listings->paginate(100), false));
  }

  public function testPaginateIsOnlySupportedForSomeResources(): void {
    $this->respond(['count' => 0, 'results' => []]);
    $sections = ShopSection::all(5);

    $this->expectException(SdkException::class);
    iterator_to_array($sections->paginate());
  }

  public function testFirstAndCount(): void {
    $this->respond(['count' => 40, 'results' => [['review_id' => 1], ['review_id' => 2]]]);
    $reviews = Review::all(5);

    $this->assertSame(1, $reviews->first()->review_id);
    $this->assertSame(2, $reviews->count());
    $this->assertSame(40, $reviews->count, 'The count property is Etsy\'s total.');
  }

  public function testShopFeaturedListings(): void {
    $shop = new Shop(json_decode('{"shop_id": 5}'));

    $this->respond(['count' => 0, 'results' => []]);
    $shop->featuredListings();
    $this->assertLastRequest('GET', '/application/shops/5/listings/featured');

    $this->respond(['count' => 0, 'results' => []]);
    $shop->featuedListings();
    $this->assertLastRequest('GET', '/application/shops/5/listings/featured');
  }
}
