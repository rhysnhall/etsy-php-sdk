# API Reference

Comprehensive reference for all resources in the Etsy PHP SDK.

## Table of Contents

- [Resource Overview](#resource-overview)
- [BuyerTaxonomy](#buyertaxonomy)
- [BuyerTaxonomyProperty](#buyertaxonomyproperty)
- [HolidayPreference](#holidaypreference)
- [LedgerEntry](#ledgerentry)
- [Listing](#listing)
- [ListingFile](#listingfile)
- [ListingImage](#listingimage)
- [ListingInventory](#listinginventory)
- [ListingOffering](#listingoffering)
- [ListingProduct](#listingproduct)
- [ListingProperty](#listingproperty)
- [ListingTranslation](#listingtranslation)
- [ListingVariationImage](#listingvariationimage)
- [ListingVideo](#listingvideo)
- [Payment](#payment)
- [ProcessingProfile](#processingprofile)
- [ProductionPartner](#productionpartner)
- [Receipt](#receipt)
- [ReturnPolicy](#returnpolicy)
- [Review](#review)
- [SellerTaxonomy](#sellertaxonomy)
- [SellerTaxonomyProperty](#sellertaxonomyproperty)
- [Shipment](#shipment)
- [ShippingCarrier](#shippingcarrier)
- [ShippingDestination](#shippingdestination)
- [ShippingProfile](#shippingprofile)
- [ShippingUpgrade](#shippingupgrade)
- [Shop](#shop)
- [ShopSection](#shopsection)
- [Transaction](#transaction)
- [User](#user)
- [UserAddress](#useraddress)

## Resource Overview

All resources extend the base `Resource` class and follow common patterns:

### Common Patterns

**Static Methods**: Create, read, update, delete operations
```php
$resource = ResourceName::get($id);
$resources = ResourceName::all();
$resource = ResourceName::create($data);
$resource = ResourceName::update($id, $data);
$deleted = ResourceName::delete($id);
```

**Instance Methods**: Operate on a specific resource instance
```php
$resource->save();
$related = $resource->relatedResource();
```

**Property Access**: Access resource properties directly
```php
$value = $resource->property_name;
$resource->property_name = $newValue;
```

**Conversion Methods**: Available on all resources
```php
$array = $resource->toArray();
$json = $resource->toJson();
```

### Return Types

- **Single Resource**: Returns instance of the resource class or `null`
- **Multiple Resources**: Returns `Collection` instance
- **Boolean Operations**: Returns `true` or `false`
- **Associations**: Returns related resource instance or collection

---

## BuyerTaxonomy

Represents Etsy's buyer taxonomy for product categorization.

**Namespace**: `Etsy\Resources\BuyerTaxonomy`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/BuyerTaxonomy`](https://developers.etsy.com/documentation/reference#tag/BuyerTaxonomy)

### Methods

#### `BuyerTaxonomy::all()`

Get all buyer taxonomy nodes.

```php
public static function all(): Collection
```

**Returns**: Collection of BuyerTaxonomy resources

**Example**:
```php
$taxonomies = \Etsy\Resources\BuyerTaxonomy::all();

foreach ($taxonomies->data as $taxonomy) {
    echo "{$taxonomy->id}: {$taxonomy->name}\n";
}
```

---

## BuyerTaxonomyProperty

Properties associated with buyer taxonomy nodes.

**Namespace**: `Etsy\Resources\BuyerTaxonomyProperty`

### Methods

#### `BuyerTaxonomyProperty::all()`

Get all buyer taxonomy properties for a taxonomy.

```php
public static function all(int $taxonomy_id): Collection
```

**Parameters**:
- `$taxonomy_id` (int): The taxonomy node ID

**Returns**: Collection of BuyerTaxonomyProperty resources

**Example**:
```php
$properties = \Etsy\Resources\BuyerTaxonomyProperty::all(1234);
```

---

## HolidayPreference

Shop holiday preferences for scheduling closures.

**Namespace**: `Etsy\Resources\HolidayPreference`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/Shop-HolidayPreferences`](https://developers.etsy.com/documentation/reference#tag/Shop-HolidayPreferences)

### Methods

#### `HolidayPreference::all()`

Get all holiday preferences for a shop.

```php
public static function all(int $shop_id): Collection
```

**Parameters**:
- `$shop_id` (int): The shop ID

**Returns**: Collection of HolidayPreference resources

#### `HolidayPreference::get()`

Get a specific holiday preference.

```php
public static function get(int $shop_id, int $preference_id): ?HolidayPreference
```

**Parameters**:
- `$shop_id` (int): The shop ID
- `$preference_id` (int): The holiday preference ID

**Returns**: HolidayPreference or null

#### `HolidayPreference::create()`

Create a new holiday preference.

```php
public static function create(int $shop_id, array $data): ?HolidayPreference
```

**Parameters**:
- `$shop_id` (int): The shop ID
- `$data` (array): Holiday preference data

**Returns**: HolidayPreference or null

#### `HolidayPreference::update()`

Update a holiday preference.

```php
public static function update(int $shop_id, int $preference_id, array $data): ?HolidayPreference
```

#### `HolidayPreference::delete()`

Delete a holiday preference.

```php
public static function delete(int $shop_id, int $preference_id): bool
```

**Example**:
```php
use Etsy\Resources\HolidayPreference;

// Get all preferences
$preferences = HolidayPreference::all($shopId);

// Create a new preference
$preference = HolidayPreference::create($shopId, [
    'date' => '2024-12-25',
    'note' => 'Closed for Christmas'
]);

// Update
$preference = HolidayPreference::update($shopId, $preferenceId, [
    'note' => 'Closed for the holidays'
]);

// Delete
$deleted = HolidayPreference::delete($shopId, $preferenceId);
```

---

## LedgerEntry

Shop ledger entries (payments, fees, etc.).

**Namespace**: `Etsy\Resources\LedgerEntry`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/Ledger`](https://developers.etsy.com/documentation/reference#tag/Ledger)

### Methods

#### `LedgerEntry::all()`

Get all ledger entries for a shop.

```php
public static function all(int $shop_id, array $params = []): Collection
```

**Parameters**:
- `$shop_id` (int): The shop ID
- `$params` (array): Query parameters (optional)
  - `min_created` (int): Minimum creation timestamp
  - `max_created` (int): Maximum creation timestamp
  - `limit` (int): Number of results (default: 25, max: 100)
  - `offset` (int): Pagination offset

**Returns**: Collection of LedgerEntry resources

#### `LedgerEntry::get()`

Get a specific ledger entry.

```php
public static function get(int $shop_id, int $ledger_entry_id): ?LedgerEntry
```

**Example**:
```php
use Etsy\Resources\LedgerEntry;

// Get recent ledger entries
$entries = LedgerEntry::all($shopId, [
    'min_created' => strtotime('-30 days'),
    'limit' => 50
]);

foreach ($entries->data as $entry) {
    echo "{$entry->create_date}: {$entry->entry_type} - {$entry->amount} {$entry->currency}\n";
}

// Get specific entry
$entry = LedgerEntry::get($shopId, $entryId);
```

---

## Listing

Etsy product listings.

**Namespace**: `Etsy\Resources\Listing`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/ShopListing`](https://developers.etsy.com/documentation/reference#tag/ShopListing)

### Properties

**Saveable Properties**:
```php
'image_ids', 'title', 'description', 'materials', 'should_auto_renew',
'shipping_profile_id', 'return_policy_id', 'shop_section_id', 'item_weight',
'item_length', 'item_width', 'item_height', 'item_weight_unit',
'item_dimensions_unit', 'is_taxable', 'taxonomy_id', 'tags', 'who_made',
'when_made', 'featured_rank', 'is_personalizable', 'personalization_is_required',
'personalization_char_count_max', 'personalization_instructions', 'state',
'is_supply', 'production_partner_ids', 'type'
```

**Associations**:
- `shop`: Shop resource
- `user`: User resource
- `images`: Array of ListingImage resources
- `shipping_profile`: ShippingProfile resource
- `videos`: Array of ListingVideo resources

### Static Methods

#### `Listing::all()`

Get all active listings on Etsy.

```php
public static function all(array $params = []): Collection
```

**Parameters**:
- `$params` (array): Query parameters
  - `limit` (int): Results per page (max: 100)
  - `offset` (int): Pagination offset
  - `keywords` (string): Search keywords
  - `sort_on` (string): Field to sort on
  - `sort_order` (string): 'asc' or 'desc'
  - `min_price` (float): Minimum price
  - `max_price` (float): Maximum price
  - `taxonomy_id` (int): Category ID
  - `includes` (array): Include associations

**Returns**: Collection of Listing resources

#### `Listing::allByIds()`

Get listings by their IDs (up to 100).

```php
public static function allByIds(string|array $listing_ids, array $includes = []): Collection
```

**Parameters**:
- `$listing_ids` (string|array): Single ID or array of listing IDs
- `$includes` (array): Associations to include

#### `Listing::allByShop()`

Get all listings for a shop.

```php
public static function allByShop(int $shop_id, array $params = []): Collection
```

#### `Listing::allActiveByShop()`

Get all active listings for a shop.

```php
public static function allActiveByShop(int $shop_id, array $params = []): Collection
```

#### `Listing::allFeaturedByShop()`

Get featured listings for a shop.

```php
public static function allFeaturedByShop(int $shop_id, array $params = []): Collection
```

#### `Listing::allByReceipt()`

Get listings from a specific receipt.

```php
public static function allByReceipt(int $shop_id, int $receipt_id, array $params = []): Collection
```

#### `Listing::allByReturnPolicy()`

Get listings using a specific return policy.

```php
public static function allByReturnPolicy(int $shop_id, int $policy_id): Collection
```

#### `Listing::allByShopSections()`

Get listings in specific shop sections.

```php
public static function allByShopSections(int $shop_id, array|int $section_ids, array $params = []): Collection
```

#### `Listing::get()`

Get a single listing.

```php
public static function get(int $listing_id, array $params = []): ?Listing
```

**Parameters**:
- `$listing_id` (int): The listing ID
- `$params` (array): Query parameters
  - `includes` (array): Include associations like 'Shop', 'Images', 'User', 'Shipping', 'Videos'

#### `Listing::create()`

Create a draft listing.

```php
public static function create(int $shop_id, array $data): ?Listing
```

**Parameters**:
- `$shop_id` (int): The shop ID
- `$data` (array): Listing data (see Etsy API docs for required fields)

**Required Fields**:
- `quantity` (int)
- `title` (string): Max 140 characters
- `description` (string)
- `price` (float)
- `who_made` (string): 'i_did', 'someone_else', 'collective'
- `when_made` (string): See Etsy docs for valid values
- `taxonomy_id` (int)

#### `Listing::update()`

Update a listing.

```php
public static function update(int $shop_id, int $listing_id, array $data): ?Listing
```

#### `Listing::delete()`

Delete a listing.

```php
public static function delete(int $listing_id): bool
```

### Instance Methods

#### `save()`

Save changes to the listing.

```php
public function save(?array $data = null): Listing
```

**Parameters**:
- `$data` (array|null): Optional data to save. If null, saves tracked changes.

**Example**:
```php
$listing = Listing::get($listingId);
$listing->title = "New Title";
$listing->description = "New Description";
$listing->save();  // Only sends changed fields
```

#### `reviews()`

Get reviews for the listing.

```php
public function reviews(array $params = []): Collection
```

**Returns**: Collection of Review resources

#### `transactions()`

Get transactions for the listing.

```php
public function transactions(int $receipt_id, array $params = []): Collection
```

**Returns**: Collection of Transaction resources

#### `properties()`

Get properties for the listing.

```php
public function properties(int $product_id): Collection
```

**Returns**: Collection of ListingProperty resources

#### `files()`

Get downloadable files for the listing.

```php
public function files(): Collection
```

**Returns**: Collection of ListingFile resources

#### `file()`

Get a specific file.

```php
public function file(int $file_id): ?ListingFile
```

#### `deleteFile()`

Delete a file from the listing.

```php
public function deleteFile(int $file_id): bool
```

#### `linkFile()`

Link an existing file to the listing.

```php
public function linkFile(int $file_id, int $rank = 1): ?ListingFile
```

#### `uploadFile()`

Upload a new file.

```php
public function uploadFile(mixed $file, string $name, array $options = []): ?ListingFile
```

**Parameters**:
- `$file` (mixed): File path or file resource
- `$name` (string): Filename
- `$options` (array): Additional options

#### `images()`

Get images for the listing.

```php
public function images(): Collection
```

**Returns**: Collection of ListingImage resources

#### `image()`

Get a specific image.

```php
public function image(int $image_id): ?ListingImage
```

#### `linkImage()`

Link an existing image.

```php
public function linkImage(int $image_id, array $options = []): ?ListingImage
```

#### `uploadImage()`

Upload a new image.

```php
public function uploadImage(mixed $image, array $options): ?ListingImage
```

**Parameters**:
- `$image` (mixed): Image path or resource
- `$options` (array): Additional options like 'rank', 'overwrite', 'is_watermarked'

#### `variationImages()`

Get variation images.

```php
public function variationImages(): Collection
```

#### `videos()`

Get videos for the listing.

```php
public function videos(): Collection
```

#### `video()`

Get a specific video.

```php
public function video(int $video_id): ?ListingVideo
```

#### `linkVideo()`

Link an existing video.

```php
public function linkVideo(int $video_id): ?ListingVideo
```

#### `uploadVideo()`

Upload a new video.

```php
public function uploadVideo(mixed $video, string $name): ?ListingVideo
```

#### `inventory()`

Get listing inventory.

```php
public function inventory(array $params = []): ?ListingInventory
```

#### `product()`

Get a specific product.

```php
public function product(int $product_id): ?ListingProduct
```

#### `translation()`

Get a translation for the listing.

```php
public function translation(string $language): ?ListingTranslation
```

**Parameters**:
- `$language` (string): Language code (e.g., 'en', 'es', 'fr')

### Examples

```php
use Etsy\Resources\Listing;

// Search all listings
$listings = Listing::all([
    'keywords' => 'handmade jewelry',
    'min_price' => 10.00,
    'max_price' => 100.00,
    'limit' => 25
]);

// Get shop listings
$shopListings = Listing::allByShop($shopId, [
    'state' => 'active',
    'includes' => ['Images', 'Shop']
]);

// Get a single listing with associations
$listing = Listing::get($listingId, [
    'includes' => ['Images', 'Shop', 'User', 'Shipping', 'Videos']
]);

// Access associations
$shop = $listing->shop;  // Shop resource
$images = $listing->images;  // Array of ListingImage resources

// Create a listing
$newListing = Listing::create($shopId, [
    'quantity' => 10,
    'title' => 'Handmade Ceramic Mug',
    'description' => 'Beautiful handcrafted ceramic mug...',
    'price' => 24.99,
    'who_made' => 'i_did',
    'when_made' => '2020_2023',
    'taxonomy_id' => 1234,
    'shipping_profile_id' => 5678,
    'return_policy_id' => 9012,
    'tags' => ['ceramic', 'mug', 'handmade', 'pottery']
]);

// Update a listing
$listing = Listing::get($listingId);
$listing->title = "Updated Title";
$listing->price = 29.99;
$listing->save();

// Or use static update
$listing = Listing::update($shopId, $listingId, [
    'title' => 'Updated Title',
    'price' => 29.99
]);

// Upload images
$listing->uploadImage('./path/to/image.jpg', [
    'rank' => 1,
    'is_watermarked' => false
]);

// Get listing images
$images = $listing->images();
foreach ($images->data as $image) {
    echo $image->url_570xN . "\n";
}

// Get inventory
$inventory = $listing->inventory();

// Get translation
$translation = $listing->translation('es');

// Delete listing
$deleted = Listing::delete($listingId);
```

---

## ListingFile

Downloadable files attached to listings.

**Namespace**: `Etsy\Resources\ListingFile`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/ShopListing-File`](https://developers.etsy.com/documentation/reference#tag/ShopListing-File)

### Methods

#### `ListingFile::all()`

Get all files for a listing.

```php
public static function all(int $shop_id, int $listing_id): Collection
```

#### `ListingFile::get()`

Get a specific file.

```php
public static function get(int $shop_id, int $listing_id, int $file_id): ?ListingFile
```

#### `ListingFile::create()`

Upload or link a file to a listing.

```php
public static function create(int $shop_id, int $listing_id, array $data): ?ListingFile
```

**Parameters**:
- `$data` (array):
  - `file` (mixed): File path or resource (for upload)
  - `name` (string): Filename (for upload)
  - `listing_file_id` (int): Existing file ID (for linking)
  - `rank` (int): Display order (optional)

#### `ListingFile::delete()`

Delete a file from a listing.

```php
public static function delete(int $shop_id, int $listing_id, int $file_id): bool
```

**Example**:
```php
use Etsy\Resources\ListingFile;

// Upload a file
$file = ListingFile::create($shopId, $listingId, [
    'file' => './downloads/template.pdf',
    'name' => 'Template.pdf'
]);

// Get all files
$files = ListingFile::all($shopId, $listingId);

// Delete a file
$deleted = ListingFile::delete($shopId, $listingId, $fileId);
```

---

## ListingImage

Images attached to listings.

**Namespace**: `Etsy\Resources\ListingImage`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/ShopListing-Image`](https://developers.etsy.com/documentation/reference#tag/ShopListing-Image)

### Methods

#### `ListingImage::all()`

Get all images for a listing.

```php
public static function all(int $listing_id): Collection
```

#### `ListingImage::get()`

Get a specific image.

```php
public static function get(int $listing_id, int $image_id): ?ListingImage
```

#### `ListingImage::create()`

Upload or link an image.

```php
public static function create(int $shop_id, int $listing_id, array $data): ?ListingImage
```

**Parameters**:
- `$data` (array):
  - `image` (mixed): Image path or resource (for upload)
  - `listing_image_id` (int): Existing image ID (for linking)
  - `rank` (int): Display order (1-10)
  - `overwrite` (bool): Replace image at same rank
  - `is_watermarked` (bool): Is image watermarked
  - `alt_text` (string): Alt text for accessibility

#### `ListingImage::delete()`

Delete an image.

```php
public static function delete(int $shop_id, int $listing_id, int $image_id): bool
```

**Example**:
```php
use Etsy\Resources\ListingImage;

// Upload an image
$image = ListingImage::create($shopId, $listingId, [
    'image' => './photos/product-1.jpg',
    'rank' => 1,
    'alt_text' => 'Ceramic mug - front view'
]);

// Get all images
$images = ListingImage::all($listingId);

foreach ($images->data as $img) {
    echo "Rank {$img->rank}: {$img->url_570xN}\n";
}

// Delete an image
$deleted = ListingImage::delete($shopId, $listingId, $imageId);
```

---

## ListingInventory

Inventory management for listings.

**Namespace**: `Etsy\Resources\ListingInventory`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/ShopListing-Inventory`](https://developers.etsy.com/documentation/reference#tag/ShopListing-Inventory)

### Methods

#### `ListingInventory::get()`

Get inventory for a listing.

```php
public static function get(int $listing_id, array $params = []): ?ListingInventory
```

**Parameters**:
- `$listing_id` (int): The listing ID
- `$params` (array): Query parameters
  - `includes` (array): Include 'Offering'

#### `ListingInventory::update()`

Update listing inventory.

```php
public static function update(int $listing_id, array $data): ?ListingInventory
```

**Parameters**:
- `$data` (array): Inventory data including products and offerings

**Example**:
```php
use Etsy\Resources\ListingInventory;

// Get inventory
$inventory = ListingInventory::get($listingId);

// Access products and offerings
foreach ($inventory->products as $product) {
    foreach ($product->offerings as $offering) {
        echo "SKU: {$offering->sku}, Price: {$offering->price}\n";
    }
}

// Update inventory
$updated = ListingInventory::update($listingId, [
    'products' => [
        [
            'product_id' => 123,
            'sku' => 'SKU-001',
            'offerings' => [
                [
                    'offering_id' => 456,
                    'quantity' => 10,
                    'price' => 25.00
                ]
            ]
        ]
    ]
]);
```

---

## ListingOffering

Product offerings (variants) within a listing.

**Namespace**: `Etsy\Resources\ListingOffering`

### Properties

Represents a specific variant/offering of a product with its own price and quantity.

**Example**:
```php
// Access through ListingProduct or ListingInventory
$inventory = ListingInventory::get($listingId);
foreach ($inventory->products as $product) {
    foreach ($product->offerings as $offering) {
        echo "Price: {$offering->price}, Quantity: {$offering->quantity}\n";
    }
}
```

---

## ListingProduct

Products within a listing's inventory.

**Namespace**: `Etsy\Resources\ListingProduct`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/ShopListing-Product`](https://developers.etsy.com/documentation/reference#tag/ShopListing-Product)

### Methods

#### `ListingProduct::get()`

Get a specific product.

```php
public static function get(int $listing_id, int $product_id): ?ListingProduct
```

**Example**:
```php
use Etsy\Resources\ListingProduct;

$product = ListingProduct::get($listingId, $productId);

echo "SKU: {$product->sku}\n";
echo "Price: {$product->offerings[0]->price}\n";
```

---

## ListingProperty

Custom properties for listings.

**Namespace**: `Etsy\Resources\ListingProperty`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/ShopListing-Property`](https://developers.etsy.com/documentation/reference#tag/ShopListing-Property)

### Methods

#### `ListingProperty::all()`

Get properties for a listing.

```php
public static function all(int $shop_id, int $listing_id, int $product_id): Collection
```

#### `ListingProperty::update()`

Update listing properties.

```php
public static function update(int $shop_id, int $listing_id, array $data): Collection
```

#### `ListingProperty::delete()`

Delete a property.

```php
public static function delete(int $shop_id, int $listing_id, int $property_id): bool
```

**Example**:
```php
use Etsy\Resources\ListingProperty;

// Get all properties
$properties = ListingProperty::all($shopId, $listingId, $productId);

// Update properties
$properties = ListingProperty::update($shopId, $listingId, [
    [
        'property_id' => 123,
        'value_ids' => [456, 789],
        'values' => ['Red', 'Blue']
    ]
]);
```

---

## ListingTranslation

Translations for listings in different languages.

**Namespace**: `Etsy\Resources\ListingTranslation`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/ShopListing-Translation`](https://developers.etsy.com/documentation/reference#tag/ShopListing-Translation)

### Properties

**Saveable Properties**:
```php
'title', 'description', 'tags'
```

### Methods

#### `ListingTranslation::get()`

Get a translation for a listing.

```php
public static function get(int $shop_id, int $listing_id, string $language): ?ListingTranslation
```

**Parameters**:
- `$shop_id` (int): The shop ID
- `$listing_id` (int): The listing ID
- `$language` (string): Language code (e.g., 'es', 'fr', 'de')

#### `ListingTranslation::create()`

Create a translation.

```php
public static function create(int $shop_id, int $listing_id, string $language, array $data): ?ListingTranslation
```

**Parameters**:
- `$data` (array):
  - `title` (string): Translated title
  - `description` (string): Translated description
  - `tags` (array): Translated tags

#### `ListingTranslation::update()`

Update a translation.

```php
public static function update(int $shop_id, int $listing_id, string $language, array $data): ?ListingTranslation
```

#### `save()`

Save changes to the translation (instance method).

```php
public function save(?array $data = null): ListingTranslation
```

**Example**:
```php
use Etsy\Resources\ListingTranslation;

// Create Spanish translation
$translation = ListingTranslation::create($shopId, $listingId, 'es', [
    'title' => 'Taza de cerámica hecha a mano',
    'description' => 'Hermosa taza de cerámica artesanal...',
    'tags' => ['cerámica', 'taza', 'artesanía']
]);

// Get translation
$spanish = ListingTranslation::get($shopId, $listingId, 'es');

// Update translation
$spanish->title = "Título actualizado";
$spanish->save();
```

---

## ListingVariationImage

Images for listing variations.

**Namespace**: `Etsy\Resources\ListingVariationImage`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/ShopListing-VariationImage`](https://developers.etsy.com/documentation/reference#tag/ShopListing-VariationImage)

### Methods

#### `ListingVariationImage::all()`

Get variation images for a listing.

```php
public static function all(int $shop_id, int $listing_id): Collection
```

#### `ListingVariationImage::update()`

Update variation images.

```php
public static function update(int $shop_id, int $listing_id, array $data): Collection
```

**Parameters**:
- `$data` (array): Array of variation image mappings

**Example**:
```php
use Etsy\Resources\ListingVariationImage;

// Get variation images
$variationImages = ListingVariationImage::all($shopId, $listingId);

// Update variation images
$updated = ListingVariationImage::update($shopId, $listingId, [
    [
        'property_id' => 200,
        'value_id' => 301,
        'image_id' => 12345
    ]
]);
```

---

## ListingVideo

Videos attached to listings.

**Namespace**: `Etsy\Resources\ListingVideo`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/ShopListing-Video`](https://developers.etsy.com/documentation/reference#tag/ShopListing-Video)

### Methods

#### `ListingVideo::all()`

Get all videos for a listing.

```php
public static function all(int $listing_id): Collection
```

#### `ListingVideo::get()`

Get a specific video.

```php
public static function get(int $listing_id, int $video_id): ?ListingVideo
```

#### `ListingVideo::create()`

Upload or link a video.

```php
public static function create(int $shop_id, int $listing_id, array $data): ?ListingVideo
```

**Parameters**:
- `$data` (array):
  - `video` (mixed): Video path or resource (for upload)
  - `name` (string): Filename (for upload)
  - `video_id` (int): Existing video ID (for linking)

#### `ListingVideo::delete()`

Delete a video.

```php
public static function delete(int $shop_id, int $listing_id, int $video_id): bool
```

**Example**:
```php
use Etsy\Resources\ListingVideo;

// Upload video
$video = ListingVideo::create($shopId, $listingId, [
    'video' => './videos/product-demo.mp4',
    'name' => 'product-demo.mp4'
]);

// Get all videos
$videos = ListingVideo::all($listingId);

// Delete video
$deleted = ListingVideo::delete($shopId, $listingId, $videoId);
```

---

## Payment

Shop payments.

**Namespace**: `Etsy\Resources\Payment`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/Payment`](https://developers.etsy.com/documentation/reference#tag/Payment)

### Methods

#### `Payment::all()`

Get payments for a shop.

```php
public static function all(int $shop_id, int|array $payment_ids): Collection
```

**Parameters**:
- `$shop_id` (int): The shop ID
- `$payment_ids` (int|array): Single payment ID or array of payment IDs

#### `Payment::allByReceipt()`

Get payments for a specific receipt.

```php
public static function allByReceipt(int $shop_id, int $receipt_id): Collection
```

**Example**:
```php
use Etsy\Resources\Payment;

// Get specific payments
$payments = Payment::all($shopId, [123, 456, 789]);

// Get payments for a receipt
$receiptPayments = Payment::allByReceipt($shopId, $receiptId);

foreach ($receiptPayments->data as $payment) {
    echo "Amount: {$payment->amount} {$payment->currency}\n";
}
```

---

## ProcessingProfile

Shop order processing profiles.

**Namespace**: `Etsy\Resources\ProcessingProfile`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/Shop-ProcessingProfile`](https://developers.etsy.com/documentation/reference#tag/Shop-ProcessingProfile)

### Methods

#### `ProcessingProfile::all()`

Get all processing profiles for a shop.

```php
public static function all(int $shop_id): Collection
```

#### `ProcessingProfile::get()`

Get a specific processing profile.

```php
public static function get(int $shop_id, int $profile_id): ?ProcessingProfile
```

**Example**:
```php
use Etsy\Resources\ProcessingProfile;

$profiles = ProcessingProfile::all($shopId);

foreach ($profiles->data as $profile) {
    echo "{$profile->name}: {$profile->processing_time_min}-{$profile->processing_time_max} days\n";
}
```

---

## ProductionPartner

Production partners for a shop.

**Namespace**: `Etsy\Resources\ProductionPartner`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/Shop-ProductionPartner`](https://developers.etsy.com/documentation/reference#tag/Shop-ProductionPartner)

### Methods

#### `ProductionPartner::all()`

Get all production partners for a shop.

```php
public static function all(int $shop_id): Collection
```

**Example**:
```php
use Etsy\Resources\ProductionPartner;

$partners = ProductionPartner::all($shopId);

foreach ($partners->data as $partner) {
    echo "{$partner->partner_name} - {$partner->location}\n";
}
```

---

## Receipt

Shop receipts (orders).

**Namespace**: `Etsy\Resources\Receipt`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/Shop-Receipt`](https://developers.etsy.com/documentation/reference#tag/Shop-Receipt)

### Properties

**Saveable Properties**:
```php
'was_shipped', 'was_paid'
```

**Associations**:
- `shipments`: Array of Shipment resources

### Methods

#### `Receipt::all()`

Get all receipts for a shop.

```php
public static function all(int $shop_id, array $params = []): Collection
```

**Parameters**:
- `$shop_id` (int): The shop ID
- `$params` (array):
  - `min_created` (int): Minimum creation timestamp
  - `max_created` (int): Maximum creation timestamp
  - `min_last_modified` (int): Minimum last modified timestamp
  - `max_last_modified` (int): Maximum last modified timestamp
  - `limit` (int): Results per page (max: 100)
  - `offset` (int): Pagination offset
  - `was_paid` (bool): Filter by payment status
  - `was_shipped` (bool): Filter by shipping status

#### `Receipt::get()`

Get a specific receipt.

```php
public static function get(int $shop_id, int $receipt_id): ?Receipt
```

#### `Receipt::update()`

Update a receipt.

```php
public static function update(int $shop_id, int $receipt_id, array $data): ?Receipt
```

**Parameters**:
- `$data` (array):
  - `was_shipped` (bool)
  - `was_paid` (bool)

#### `save()`

Save changes to the receipt (instance method).

```php
public function save(?array $data = null): Receipt
```

#### `transactions()`

Get transactions for the receipt.

```php
public function transactions(array $params = []): Collection
```

#### `shipment()`

Create a shipment for the receipt.

```php
public function shipment(array $data): ?Shipment
```

**Example**:
```php
use Etsy\Resources\Receipt;

// Get recent receipts
$receipts = Receipt::all($shopId, [
    'min_created' => strtotime('-30 days'),
    'was_paid' => true,
    'limit' => 50
]);

// Get specific receipt
$receipt = Receipt::get($shopId, $receiptId);

echo "Receipt #{$receipt->receipt_id}\n";
echo "Buyer: {$receipt->name}\n";
echo "Total: {$receipt->grandtotal} {$receipt->currency_code}\n";

// Mark as shipped
$receipt->was_shipped = true;
$receipt->save();

// Create shipment
$shipment = $receipt->shipment([
    'tracking_code' => 'ABC123456789',
    'carrier_name' => 'USPS'
]);

// Get transactions
$transactions = $receipt->transactions();
```

---

## ReturnPolicy

Shop return policies.

**Namespace**: `Etsy\Resources\ReturnPolicy`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/Shop-ReturnPolicy`](https://developers.etsy.com/documentation/reference#tag/Shop-ReturnPolicy)

### Properties

**Saveable Properties**:
```php
'accepts_returns', 'accepts_exchanges', 'return_deadline'
```

### Methods

#### `ReturnPolicy::all()`

Get all return policies for a shop.

```php
public static function all(int $shop_id): Collection
```

#### `ReturnPolicy::get()`

Get a specific return policy.

```php
public static function get(int $shop_id, int $policy_id): ?ReturnPolicy
```

#### `ReturnPolicy::create()`

Create a return policy.

```php
public static function create(int $shop_id, array $data): ?ReturnPolicy
```

**Parameters**:
- `$data` (array):
  - `accepts_returns` (bool)
  - `accepts_exchanges` (bool)
  - `return_deadline` (int): Days until return deadline

#### `ReturnPolicy::update()`

Update a return policy.

```php
public static function update(int $shop_id, int $policy_id, array $data): ?ReturnPolicy
```

#### `ReturnPolicy::delete()`

Delete a return policy.

```php
public static function delete(int $shop_id, int $policy_id): bool
```

#### `save()`

Save changes to the policy (instance method).

```php
public function save(?array $data = null): ReturnPolicy
```

**Example**:
```php
use Etsy\Resources\ReturnPolicy;

// Create a return policy
$policy = ReturnPolicy::create($shopId, [
    'accepts_returns' => true,
    'accepts_exchanges' => true,
    'return_deadline' => 30
]);

// Get all policies
$policies = ReturnPolicy::all($shopId);

// Update policy
$policy = ReturnPolicy::get($shopId, $policyId);
$policy->return_deadline = 60;
$policy->save();

// Delete policy
$deleted = ReturnPolicy::delete($shopId, $policyId);
```

---

## Review

Shop and listing reviews.

**Namespace**: `Etsy\Resources\Review`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/Review`](https://developers.etsy.com/documentation/reference#tag/Review)

### Methods

#### `Review::all()`

Get reviews for a shop.

```php
public static function all(int $shop_id, array $params = []): Collection
```

**Parameters**:
- `$shop_id` (int): The shop ID
- `$params` (array):
  - `min_created` (int): Minimum creation timestamp
  - `max_created` (int): Maximum creation timestamp
  - `limit` (int): Results per page (max: 100)
  - `offset` (int): Pagination offset

#### `Review::allByListing()`

Get reviews for a specific listing.

```php
public static function allByListing(int $listing_id, array $params = []): Collection
```

**Example**:
```php
use Etsy\Resources\Review;

// Get shop reviews
$reviews = Review::all($shopId, [
    'limit' => 25,
    'min_created' => strtotime('-90 days')
]);

foreach ($reviews->data as $review) {
    echo "Rating: {$review->rating}/5\n";
    echo "Review: {$review->review}\n";
    echo "---\n";
}

// Paginate through reviews
foreach ($reviews->paginate(100) as $review) {
    // Process each review
}

// Get listing reviews
$listingReviews = Review::allByListing($listingId);
```

---

## SellerTaxonomy

Etsy's seller taxonomy for product categorization.

**Namespace**: `Etsy\Resources\SellerTaxonomy`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/SellerTaxonomy`](https://developers.etsy.com/documentation/reference#tag/SellerTaxonomy)

### Methods

#### `SellerTaxonomy::all()`

Get all seller taxonomy nodes.

```php
public static function all(): Collection
```

**Example**:
```php
use Etsy\Resources\SellerTaxonomy;

$taxonomies = SellerTaxonomy::all();

foreach ($taxonomies->data as $taxonomy) {
    echo "{$taxonomy->id}: {$taxonomy->name} (Level: {$taxonomy->level})\n";
}
```

---

## SellerTaxonomyProperty

Properties for seller taxonomy nodes.

**Namespace**: `Etsy\Resources\SellerTaxonomyProperty`

### Methods

#### `SellerTaxonomyProperty::all()`

Get properties for a taxonomy node.

```php
public static function all(int $taxonomy_id): Collection
```

**Example**:
```php
use Etsy\Resources\SellerTaxonomyProperty;

$properties = SellerTaxonomyProperty::all($taxonomyId);

foreach ($properties->data as $property) {
    echo "{$property->name}: {$property->display_name}\n";
}
```

---

## Shipment

Shipping information for receipts.

**Namespace**: `Etsy\Resources\Shipment`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/Shop-Receipt-Shipment`](https://developers.etsy.com/documentation/reference#tag/Shop-Receipt-Shipment)

### Methods

#### `Shipment::create()`

Create a shipment for a receipt.

```php
public static function create(int $shop_id, int $receipt_id, array $data): ?Shipment
```

**Parameters**:
- `$shop_id` (int): The shop ID
- `$receipt_id` (int): The receipt ID
- `$data` (array):
  - `tracking_code` (string): Tracking number
  - `carrier_name` (string): Shipping carrier
  - `send_bcc` (bool): Send BCC to buyer (optional)
  - `note_to_buyer` (string): Note to buyer (optional)

**Example**:
```php
use Etsy\Resources\Shipment;

$shipment = Shipment::create($shopId, $receiptId, [
    'tracking_code' => '1234567890',
    'carrier_name' => 'USPS',
    'send_bcc' => true,
    'note_to_buyer' => 'Your order has shipped!'
]);
```

---

## ShippingCarrier

Available shipping carriers.

**Namespace**: `Etsy\Resources\ShippingCarrier`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/ShippingCarrier`](https://developers.etsy.com/documentation/reference#tag/ShippingCarrier)

### Methods

#### `ShippingCarrier::all()`

Get all available shipping carriers.

```php
public static function all(string $origin_country_iso): Collection
```

**Parameters**:
- `$origin_country_iso` (string): Two-letter country code (e.g., 'US', 'GB')

**Example**:
```php
use Etsy\Resources\ShippingCarrier;

$carriers = ShippingCarrier::all('US');

foreach ($carriers->data as $carrier) {
    echo "{$carrier->name}\n";
}
```

---

## ShippingDestination

Shipping destinations within a shipping profile.

**Namespace**: `Etsy\Resources\ShippingDestination`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/Shop-ShippingProfile`](https://developers.etsy.com/documentation/reference#tag/Shop-ShippingProfile)

### Properties

**Saveable Properties**:
```php
'primary_cost', 'secondary_cost', 'destination_country_iso',
'destination_region', 'shipping_carrier_id', 'mail_class',
'min_delivery_days', 'max_delivery_days'
```

### Methods

#### `ShippingDestination::all()`

Get destinations for a shipping profile.

```php
public static function all(int $shop_id, int $profile_id): Collection
```

#### `ShippingDestination::create()`

Create a shipping destination.

```php
public static function create(int $shop_id, int $profile_id, array $data): ?ShippingDestination
```

**Parameters**:
- `$data` (array):
  - `primary_cost` (float): Cost for first item
  - `secondary_cost` (float): Cost for additional items
  - `destination_country_iso` (string): Two-letter country code
  - `destination_region` (string): Region (optional: 'eu', 'non_eu', etc.)
  - `shipping_carrier_id` (int): Carrier ID (optional)
  - `mail_class` (string): Mail class (optional)
  - `min_delivery_days` (int): Min delivery days (optional)
  - `max_delivery_days` (int): Max delivery days (optional)

#### `ShippingDestination::update()`

Update a shipping destination.

```php
public static function update(int $shop_id, int $profile_id, int $destination_id, array $data): ?ShippingDestination
```

#### `ShippingDestination::delete()`

Delete a shipping destination.

```php
public static function delete(int $shop_id, int $profile_id, int $destination_id): bool
```

#### `save()`

Save changes to the destination (instance method).

```php
public function save(?array $data = null): ShippingDestination
```

**Example**:
```php
use Etsy\Resources\ShippingDestination;

// Create destination
$destination = ShippingDestination::create($shopId, $profileId, [
    'destination_country_iso' => 'US',
    'primary_cost' => 5.00,
    'secondary_cost' => 2.00,
    'min_delivery_days' => 3,
    'max_delivery_days' => 7
]);

// Update destination
$destination->primary_cost = 6.00;
$destination->save();

// Delete destination
$deleted = ShippingDestination::delete($shopId, $profileId, $destinationId);
```

---

## ShippingProfile

Shop shipping profiles.

**Namespace**: `Etsy\Resources\ShippingProfile`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/Shop-ShippingProfile`](https://developers.etsy.com/documentation/reference#tag/Shop-ShippingProfile)

### Properties

**Saveable Properties**:
```php
'title', 'origin_country_iso', 'min_processing_time',
'max_processing_time', 'processing_time_unit', 'origin_postal_code'
```

**Associations**:
- `shipping_profile_destinations`: Array of ShippingDestination resources
- `shipping_profile_upgrades`: Array of ShippingUpgrade resources

### Methods

#### `ShippingProfile::all()`

Get all shipping profiles for a shop.

```php
public static function all(int $shop_id): Collection
```

#### `ShippingProfile::get()`

Get a specific shipping profile.

```php
public static function get(int $shop_id, int $profile_id): ?ShippingProfile
```

#### `ShippingProfile::create()`

Create a shipping profile.

```php
public static function create(int $shop_id, array $data): ?ShippingProfile
```

**Parameters**:
- `$data` (array):
  - `title` (string): Profile name
  - `origin_country_iso` (string): Origin country code
  - `min_processing_time` (int): Minimum processing time
  - `max_processing_time` (int): Maximum processing time
  - `processing_time_unit` (string): 'business_days' or 'weeks'
  - `origin_postal_code` (string): Origin postal code (optional)

#### `ShippingProfile::update()`

Update a shipping profile.

```php
public static function update(int $shop_id, int $profile_id, array $data): ?ShippingProfile
```

#### `ShippingProfile::delete()`

Delete a shipping profile.

```php
public static function delete(int $shop_id, int $profile_id): bool
```

#### `save()`

Save changes to the profile (instance method).

```php
public function save(?array $data = null): ShippingProfile
```

#### `destinations()`

Get destinations for the profile.

```php
public function destinations(): Collection
```

#### `destination()`

Get a specific destination.

```php
public function destination(int $destination_id): ?ShippingDestination
```

#### `upgrades()`

Get upgrades for the profile.

```php
public function upgrades(): Collection
```

#### `upgrade()`

Get a specific upgrade.

```php
public function upgrade(int $upgrade_id): ?ShippingUpgrade
```

**Example**:
```php
use Etsy\Resources\ShippingProfile;

// Create profile
$profile = ShippingProfile::create($shopId, [
    'title' => 'Standard Shipping',
    'origin_country_iso' => 'US',
    'min_processing_time' => 1,
    'max_processing_time' => 3,
    'processing_time_unit' => 'business_days',
    'origin_postal_code' => '12345'
]);

// Get all profiles
$profiles = ShippingProfile::all($shopId);

// Update profile
$profile->min_processing_time = 2;
$profile->save();

// Get destinations
$destinations = $profile->destinations();

// Get upgrades
$upgrades = $profile->upgrades();

// Delete profile
$deleted = ShippingProfile::delete($shopId, $profileId);
```

---

## ShippingUpgrade

Shipping upgrades within a shipping profile.

**Namespace**: `Etsy\Resources\ShippingUpgrade`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/Shop-ShippingProfile`](https://developers.etsy.com/documentation/reference#tag/Shop-ShippingProfile)

### Properties

**Saveable Properties**:
```php
'type', 'upgrade_name', 'price', 'secondary_price',
'shipping_carrier_id', 'mail_class', 'min_delivery_days', 'max_delivery_days'
```

### Methods

#### `ShippingUpgrade::all()`

Get upgrades for a shipping profile.

```php
public static function all(int $shop_id, int $profile_id): Collection
```

#### `ShippingUpgrade::create()`

Create a shipping upgrade.

```php
public static function create(int $shop_id, int $profile_id, array $data): ?ShippingUpgrade
```

**Parameters**:
- `$data` (array):
  - `type` (string): Type of upgrade
  - `upgrade_name` (string): Name of upgrade
  - `price` (float): Price for first item
  - `secondary_price` (float): Price for additional items
  - Additional optional fields

#### `ShippingUpgrade::update()`

Update a shipping upgrade.

```php
public static function update(int $shop_id, int $profile_id, int $upgrade_id, array $data): ?ShippingUpgrade
```

#### `ShippingUpgrade::delete()`

Delete a shipping upgrade.

```php
public static function delete(int $shop_id, int $profile_id, int $upgrade_id): bool
```

#### `save()`

Save changes to the upgrade (instance method).

```php
public function save(?array $data = null): ShippingUpgrade
```

**Example**:
```php
use Etsy\Resources\ShippingUpgrade;

// Create upgrade
$upgrade = ShippingUpgrade::create($shopId, $profileId, [
    'type' => 'expedited',
    'upgrade_name' => 'Express Shipping',
    'price' => 15.00,
    'secondary_price' => 10.00,
    'min_delivery_days' => 1,
    'max_delivery_days' => 2
]);

// Update upgrade
$upgrade->price = 20.00;
$upgrade->save();

// Delete upgrade
$deleted = ShippingUpgrade::delete($shopId, $profileId, $upgradeId);
```

---

## Shop

Etsy shops.

**Namespace**: `Etsy\Resources\Shop`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/Shop`](https://developers.etsy.com/documentation/reference#tag/Shop)

### Properties

**Saveable Properties**:
```php
'title', 'announcement', 'sale_message', 'digital_sale_message', 'policy_additional'
```

### Static Methods

#### `Shop::get()`

Get a shop by ID.

```php
public static function get(int $shop_id): ?Shop
```

#### `Shop::getByUserId()`

Get a shop by user ID.

```php
public static function getByUserId(int $user_id): ?Shop
```

#### `Shop::all()`

Search for shops by keyword.

```php
public static function all(string $keyword, array $params = []): Collection
```

**Parameters**:
- `$keyword` (string): Shop name to search for
- `$params` (array): Additional query parameters

#### `Shop::update()`

Update a shop.

```php
public static function update(int $shop_id, array $data): ?Shop
```

### Instance Methods

#### `save()`

Save changes to the shop.

```php
public function save(?array $data = null): Shop
```

#### `listings()`

Get all listings for the shop.

```php
public function listings(array $params = []): Collection
```

#### `activeListings()`

Get active listings for the shop.

```php
public function activeListings(array $params = []): Collection
```

#### `featuedListings()`

Get featured listings for the shop.

```php
public function featuedListings(array $params = []): Collection
```

#### `productionPartners()`

Get production partners.

```php
public function productionPartners(): Collection
```

#### `sections()`

Get shop sections.

```php
public function sections(): Collection
```

#### `section()`

Get a specific section.

```php
public function section(int $section_id): ?ShopSection
```

#### `returnPolicies()`

Get return policies.

```php
public function returnPolicies(): Collection
```

#### `returnPolicy()`

Get a specific return policy.

```php
public function returnPolicy(int $policy_id): ?ReturnPolicy
```

#### `shippingProfiles()`

Get shipping profiles.

```php
public function shippingProfiles(): Collection
```

#### `shippingProfile()`

Get a specific shipping profile.

```php
public function shippingProfile(int $profile_id): ?ShippingProfile
```

#### `reviews()`

Get shop reviews.

```php
public function reviews(array $params = []): Collection
```

#### `receipts()`

Get shop receipts.

```php
public function receipts(array $params = []): Collection
```

#### `receipt()`

Get a specific receipt.

```php
public function receipt(int $receipt_id): ?Receipt
```

#### `ledgerEntries()`

Get ledger entries.

```php
public function ledgerEntries(array $params = []): Collection
```

#### `ledgerEntry()`

Get a specific ledger entry.

```php
public function ledgerEntry(int $ledger_entry_id): ?LedgerEntry
```

#### `payments()`

Get payments.

```php
public function payments(int|array $payment_ids): Collection
```

#### `transactions()`

Get transactions.

```php
public function transactions(array $params = []): Collection
```

### Examples

```php
use Etsy\Resources\Shop;

// Get shop
$shop = Shop::get($shopId);

// Or get by user ID
$shop = Shop::getByUserId($userId);

// Access properties
echo "Shop: {$shop->shop_name}\n";
echo "Title: {$shop->title}\n";
echo "URL: {$shop->url}\n";

// Update shop
$shop->title = "New Shop Title";
$shop->announcement = "Shop announcement";
$shop->save();

// Or use static update
$shop = Shop::update($shopId, [
    'title' => 'New Shop Title',
    'announcement' => 'Shop announcement'
]);

// Get shop listings
$listings = $shop->activeListings(['limit' => 50]);

// Get shop sections
$sections = $shop->sections();

// Get shipping profiles
$profiles = $shop->shippingProfiles();

// Get recent receipts
$receipts = $shop->receipts([
    'min_created' => strtotime('-30 days'),
    'limit' => 100
]);

// Get shop reviews
$reviews = $shop->reviews();

// Search for shops
$shops = Shop::all('handmade jewelry', ['limit' => 25]);
```

---

## ShopSection

Sections within a shop for organizing listings.

**Namespace**: `Etsy\Resources\ShopSection`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/Shop-Section`](https://developers.etsy.com/documentation/reference#tag/Shop-Section)

### Properties

**Saveable Properties**:
```php
'title', 'rank'
```

### Methods

#### `ShopSection::all()`

Get all sections for a shop.

```php
public static function all(int $shop_id): Collection
```

#### `ShopSection::get()`

Get a specific section.

```php
public static function get(int $shop_id, int $section_id): ?ShopSection
```

#### `ShopSection::create()`

Create a shop section.

```php
public static function create(int $shop_id, array $data): ?ShopSection
```

**Parameters**:
- `$data` (array):
  - `title` (string): Section name
  - `rank` (int): Display order (optional)

#### `ShopSection::update()`

Update a shop section.

```php
public static function update(int $shop_id, int $section_id, array $data): ?ShopSection
```

#### `ShopSection::delete()`

Delete a shop section.

```php
public static function delete(int $shop_id, int $section_id): bool
```

#### `save()`

Save changes to the section (instance method).

```php
public function save(?array $data = null): ShopSection
```

**Example**:
```php
use Etsy\Resources\ShopSection;

// Create section
$section = ShopSection::create($shopId, [
    'title' => 'Mugs',
    'rank' => 1
]);

// Get all sections
$sections = ShopSection::all($shopId);

// Update section
$section->title = "Ceramic Mugs";
$section->save();

// Delete section
$deleted = ShopSection::delete($shopId, $sectionId);
```

---

## Transaction

Order transactions.

**Namespace**: `Etsy\Resources\Transaction`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/ShopReceipt-Transactions`](https://developers.etsy.com/documentation/reference#tag/ShopReceipt-Transactions)

### Methods

#### `Transaction::all()`

Get all transactions for a shop.

```php
public static function all(int $shop_id, array $params = []): Collection
```

**Parameters**:
- `$shop_id` (int): The shop ID
- `$params` (array):
  - `limit` (int): Results per page (max: 100)
  - `offset` (int): Pagination offset

#### `Transaction::allByListing()`

Get transactions for a specific listing.

```php
public static function allByListing(int $shop_id, int $listing_id, array $params = []): Collection
```

#### `Transaction::allByReceipt()`

Get transactions for a specific receipt.

```php
public static function allByReceipt(int $shop_id, int $receipt_id, array $params = []): Collection
```

#### `Transaction::get()`

Get a specific transaction.

```php
public static function get(int $shop_id, int $transaction_id): ?Transaction
```

**Example**:
```php
use Etsy\Resources\Transaction;

// Get all shop transactions
$transactions = Transaction::all($shopId, ['limit' => 50]);

// Get transactions for a listing
$listingTransactions = Transaction::allByListing($shopId, $listingId);

// Get transactions for a receipt
$receiptTransactions = Transaction::allByReceipt($shopId, $receiptId);

foreach ($receiptTransactions->data as $transaction) {
    echo "Item: {$transaction->title}\n";
    echo "Price: {$transaction->price} {$transaction->currency_code}\n";
    echo "Quantity: {$transaction->quantity}\n";
}

// Get specific transaction
$transaction = Transaction::get($shopId, $transactionId);
```

---

## User

Etsy users.

**Namespace**: `Etsy\Resources\User`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/User`](https://developers.etsy.com/documentation/reference#tag/User)

### Static Methods

#### `User::me()`

Get basic info about the authenticated user.

```php
public static function me(): ?stdClass
```

**Returns**: stdClass with user info or null

**Note**: This returns a stdClass, not a User resource.

#### `User::get()`

Get a user's profile.

```php
public static function get(string|int $user_id = null): ?User
```

**Parameters**:
- `$user_id` (string|int): User ID. If null, gets authenticated user.

### Instance Methods

#### `shop()`

Get the user's shop.

```php
public function shop(): ?Shop
```

### Examples

```php
use Etsy\Resources\User;

// Get authenticated user (basic info)
$userInfo = User::me();
echo "User ID: {$userInfo->user_id}\n";

// Get full user profile
$user = User::get();  // Authenticated user
// Or
$user = User::get($userId);  // Specific user

echo "Name: {$user->first_name} {$user->last_name}\n";
echo "Email: {$user->primary_email}\n";

// Get user's shop
$shop = $user->shop();
echo "Shop: {$shop->shop_name}\n";
```

---

## UserAddress

User addresses.

**Namespace**: `Etsy\Resources\UserAddress`

**Etsy API Docs**: [`https://developers.etsy.com/documentation/reference#tag/UserAddress`](https://developers.etsy.com/documentation/reference#tag/UserAddress)

### Methods

#### `UserAddress::all()`

Get all addresses for a user.

```php
public static function all(): Collection
```

#### `UserAddress::get()`

Get a specific address.

```php
public static function get(int $address_id): ?UserAddress
```

#### `UserAddress::delete()`

Delete an address.

```php
public static function delete(int $address_id): bool
```

**Example**:
```php
use Etsy\Resources\UserAddress;

// Get all addresses
$addresses = UserAddress::all();

foreach ($addresses->data as $address) {
    echo "{$address->name}\n";
    echo "{$address->first_line}\n";
    echo "{$address->city}, {$address->state} {$address->zip}\n";
}

// Get specific address
$address = UserAddress::get($addressId);

// Delete address
$deleted = UserAddress::delete($addressId);
```

---

## Summary

This reference covers all 32 resources in the Etsy PHP SDK. Key points:

1. **Initialization**: Always initialize Etsy class before using resources
2. **Static Methods**: For creating, fetching, updating, deleting resources
3. **Instance Methods**: For operating on specific resource instances
4. **Associations**: Automatically resolved to Resource objects
5. **Collections**: Support pagination for large datasets
6. **Change Tracking**: `save()` methods only send modified properties
7. **Type Safety**: Return types clearly defined (Resource, Collection, bool, null)

For more details, see:
- [Architecture Documentation](ARCHITECTURE.md)
- [Examples](EXAMPLES.md)
- [Official Etsy API Documentation](https://developers.etsy.com/documentation/reference)
