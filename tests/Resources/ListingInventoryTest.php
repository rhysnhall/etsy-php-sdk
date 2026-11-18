<?php

namespace Etsy\Tests\Resources;

use Etsy\Resources\{ListingInventory, ListingOffering, ListingProduct};
use Etsy\Tests\TestCase;

class ListingInventoryTest extends TestCase {

  private function inventory(): array {
    return ['products' => [[
      'product_id' => 7,
      'sku' => 'MUG-1',
      'offerings' => [
        ['offering_id' => 70, 'quantity' => 2],
        ['offering_id' => 71, 'quantity' => 5],
      ],
    ]]];
  }

  public function testGetAssignsListingIdToProducts(): void {
    $this->respond($this->inventory());
    $inventory = ListingInventory::get(11);

    $this->assertLastRequest('GET', '/application/listings/11/inventory');
    $this->assertSame(11, $inventory->listing_id);
    $this->assertInstanceOf(ListingProduct::class, $inventory->products[0]);
    $this->assertSame(11, $inventory->products[0]->listing_id);
  }

  public function testUpdateSendsJson(): void {
    $products = [[
      'sku' => 'MUG-1',
      'property_values' => [],
      'offerings' => [['price' => 12.5, 'quantity' => 3, 'is_enabled' => true]],
    ]];
    $this->respond($this->inventory());

    ListingInventory::update(11, ['products' => $products]);

    $this->assertLastRequest('PUT', '/application/listings/11/inventory');
    $this->assertSame(['products' => $products], $this->lastJson());
  }

  public function testOfferingsAreResources(): void {
    $this->respond($this->inventory());
    $offering = ListingInventory::get(11)->products[0]->offerings[0];

    $this->assertInstanceOf(ListingOffering::class, $offering);
  }

  public function testFindOfferingOnProduct(): void {
    $this->respond($this->inventory());
    $product = ListingInventory::get(11)->products[0];

    $this->assertSame(5, $product->findOffering(71)->quantity);
    $this->assertSame(2, $product->offering(70)->quantity);
    $this->assertNull($product->findOffering(999));
  }

  public function testListingOfferingGetUsesProductEndpoint(): void {
    $this->respond($this->inventory()['products'][0]);
    $offering = ListingOffering::get(11, 7, 71);

    $this->assertLastRequest('GET', '/application/listings/11/inventory/products/7');
    $this->assertSame(71, $offering->offering_id);
  }

  public function testListingOfferingGetReturnsNullForUnknownOffering(): void {
    $this->respond($this->inventory()['products'][0]);

    $this->assertNull(ListingOffering::get(11, 7, 999));
  }
}
