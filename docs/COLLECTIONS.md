# Working with Collections

Guide to working with collections of resources in the Etsy PHP SDK.

## Table of Contents

- [Overview](#overview)
- [Collection Structure](#collection-structure)
- [Accessing Data](#accessing-data)
- [Collection Methods](#collection-methods)
- [Pagination](#pagination)
- [Best Practices](#best-practices)
- [Examples](#examples)

## Overview

When API requests return multiple resources, the SDK wraps them in a `Collection` object. Collections provide convenient methods for working with sets of resources and support pagination for large datasets.

### What is a Collection?

A **Collection** is a container class that holds multiple Resource objects along with metadata about the result set.

```php
use Etsy\Resources\Listing;

$listings = Listing::all();  // Returns Collection
// Collection contains:
// - $listings->data: Array of Listing resources
// - $listings->count: Total count from API
// - Methods: first(), count(), append(), paginate(), toJson()
```

### When are Collections Returned?

Collections are returned by any method that fetches multiple resources:

```php
// All these return Collections:
$listings = Listing::all();
$shopListings = Listing::allByShop($shopId);
$reviews = Review::all($shopId);
$receipts = Receipt::all($shopId);
$images = ListingImage::all($listingId);
$sections = ShopSection::all($shopId);
```

## Collection Structure

### Properties

#### `data`

Array of Resource objects.

```php
$listings = Listing::all(['limit' => 10]);

// Array of Listing resources
foreach ($listings->data as $listing) {
    echo $listing->title . "\n";
}
```

#### `count`

Total count of available results from the Etsy API (not just current page).

```php
$listings = Listing::all();

echo "Total listings on Etsy: {$listings->count}\n";
echo "Results in this collection: " . count($listings->data) . "\n";
```

**Note**: `$collection->count` is the total from Etsy, while `$collection->count()` method returns items in current collection.

#### `resource`

The resource type name (internal property).

```php
$listings = Listing::all();
// $listings->resource === "Listing"
```

#### `uri` and `params`

Internal properties used for pagination (protected).

## Accessing Data

### Direct Array Access

Access resources directly from the `data` property:

```php
$listings = Listing::all(['limit' => 5]);

// Get first listing
$firstListing = $listings->data[0];

// Get last listing
$lastListing = $listings->data[count($listings->data) - 1];

// Iterate all listings
foreach ($listings->data as $listing) {
    echo $listing->title . "\n";
}
```

### Using first() Method

Get the first resource in the collection:

```php
$listings = Listing::all();

$firstListing = $listings->first();

if ($firstListing) {
    echo $firstListing->title;
} else {
    echo "No listings found";
}
```

### Checking if Empty

```php
$listings = Listing::all();

if (count($listings->data) === 0) {
    echo "No results found";
}

// Or use count() method
if ($listings->count() === 0) {
    echo "No results found";
}

// Or check first()
if (!$listings->first()) {
    echo "No results found";
}
```

## Collection Methods

### first()

Returns the first resource in the collection, or `false` if empty.

```php
public function first(): Resource|false
```

**Example**:
```php
$listing = Listing::all()->first();

if ($listing) {
    echo "First listing: {$listing->title}";
}
```

**Common Pattern**: Use when expecting single result
```php
// Get a specific shop
$shops = Shop::all('MyShopName');
$shop = $shops->first();
```

### count()

Returns the number of resources in the current collection.

```php
public function count(): int
```

**Important**: This is different from the `count` property.

```php
$listings = Listing::all(['limit' => 25]);

// Number of resources in this collection (max 25)
$inCollection = $listings->count();

// Total available from Etsy API
$totalAvailable = $listings->count;

echo "Showing {$inCollection} of {$totalAvailable} total listings\n";
```

### append()

Adds properties to every resource in the collection.

```php
public function append(array $data): Collection
```

**Parameters**:
- `$data` (array): Key-value pairs to add to each resource

**Returns**: The collection (for method chaining)

**Use Cases**:
- Add context data to resources
- Add calculated fields
- Add metadata

**Example**:
```php
$listings = Listing::allByShop($shopId);

// Add shop_id to each listing
$listings->append(['shop_id' => $shopId]);

// Now each listing has shop_id property
foreach ($listings->data as $listing) {
    echo "Listing {$listing->listing_id} in shop {$listing->shop_id}\n";
}
```

**Multiple Properties**:
```php
$reviews = Review::all($shopId);

$reviews->append([
    'shop_id' => $shopId,
    'fetched_at' => time(),
    'context' => 'monthly_report'
]);
```

**Chaining**:
```php
$json = Listing::all()
    ->append(['shop_id' => $shopId])
    ->toJson();
```

### toJson()

Converts all resources in the collection to JSON strings.

```php
public function toJson(): array
```

**Returns**: Array of JSON strings

**Example**:
```php
$listings = Listing::all(['limit' => 5]);

$jsonArray = $listings->toJson();

// Array of JSON strings
foreach ($jsonArray as $json) {
    echo $json . "\n";
}
```

**Saving to File**:
```php
$listings = Listing::all();
$jsonArray = $listings->toJson();

file_put_contents('listings.json', json_encode($jsonArray));
```

**Direct JSON of Entire Collection**:
```php
// For full collection as single JSON:
$listings = Listing::all();
$data = array_map(fn($l) => $l->toArray(), $listings->data);
$json = json_encode($data);
```

### paginate()

Generator that fetches multiple pages automatically.

```php
public function paginate(int $results = 100): Generator
```

**Parameters**:
- `$results` (int): Total number of results to fetch (max: 500)

**Returns**: Generator that yields individual Resource objects

**See**: [Pagination section](#pagination) for detailed information

## Pagination

### Overview

Etsy API limits results per request (typically 25-100). The `paginate()` method automatically fetches additional pages.

### Supported Resources

Only these resources support pagination:
- **Listing**
- **Shop**
- **Review**

### Basic Usage

```php
$reviews = Review::all($shopId, ['limit' => 25]);

// Fetch up to 100 reviews across multiple pages
foreach ($reviews->paginate(100) as $review) {
    echo $review->rating . ": " . $review->review . "\n";
}
```

### How It Works

1. Uses the initial collection as the first page
2. Automatically increments the `offset` parameter
3. Makes additional API requests as needed
4. Yields individual resources one at a time
5. Stops when:
   - Requested number of results is reached
   - No more results available
   - Maximum limit (500) is reached

### Parameters and Limits

```php
// Default: 100 results
$reviews->paginate();

// Custom: 200 results (across multiple pages)
$reviews->paginate(200);

// Maximum: 500 results
$reviews->paginate(500);

// If you request more than 500, it's automatically limited
$reviews->paginate(1000);  // Actually fetches 500
```

### Pagination with Query Parameters

Original query parameters are preserved during pagination:

```php
$listings = Listing::all([
    'limit' => 25,
    'keywords' => 'handmade',
    'min_price' => 10,
    'max_price' => 50
]);

// All pages will use the same filters
foreach ($listings->paginate(100) as $listing) {
    // Each listing matches the original query
}
```

### Generator Pattern

`paginate()` returns a Generator, which is memory-efficient:

```php
// Memory efficient - processes one at a time
foreach ($reviews->paginate(500) as $review) {
    processReview($review);
}

// NOT recommended - loads all into memory
$allReviews = iterator_to_array($reviews->paginate(500));
```

### Tracking Progress

```php
$reviews = Review::all($shopId, ['limit' => 25]);

$count = 0;
foreach ($reviews->paginate(200) as $review) {
    $count++;
    echo "Processing review {$count}: {$review->rating}\n";
    
    // Process review
    saveToDatabase($review);
}

echo "Processed {$count} reviews total\n";
```

### Error Handling with Pagination

```php
$listings = Listing::all(['limit' => 25]);

try {
    foreach ($listings->paginate(200) as $listing) {
        processListing($listing);
    }
} catch (\Etsy\Exception\RequestException $e) {
    echo "API error during pagination: " . $e->getMessage();
}
```

### Limitations

**Unsupported Resources**: Will throw exception
```php
use Etsy\Resources\Receipt;
use Etsy\Exception\SdkException;

$receipts = Receipt::all($shopId);

try {
    foreach ($receipts->paginate(100) as $receipt) {
        // ...
    }
} catch (SdkException $e) {
    // "The Receipt resource does not support pagination."
}
```

**Workaround for Unsupported Resources**:
```php
function manualPagination($shopId, $totalResults = 100) {
    $limit = 25;
    $results = [];
    
    for ($offset = 0; $offset < $totalResults; $offset += $limit) {
        $page = Receipt::all($shopId, [
            'limit' => $limit,
            'offset' => $offset
        ]);
        
        $results = array_merge($results, $page->data);
        
        // Stop if no more results
        if (count($page->data) < $limit) {
            break;
        }
    }
    
    return $results;
}
```

## Best Practices

### 1. Check Before Accessing

Always check if collection has data:

```php
$listings = Listing::all();

if ($listings->count() > 0) {
    $first = $listings->first();
    // Use $first
}
```

### 2. Use Pagination for Large Datasets

Don't fetch all at once if you need many results:

```php
// Good - uses pagination
foreach ($reviews->paginate(200) as $review) {
    processReview($review);
}

// Bad - tries to fetch 1000 results in one request (will fail)
$reviews = Review::all($shopId, ['limit' => 1000]);
```

### 3. Limit Results When Possible

Only fetch what you need:

```php
// Good - only fetch 10
$recentListings = Listing::all(['limit' => 10]);

// Bad - fetches default (25) even if you only need 1
$oneListings = Listing::all();
$first = $oneListings->first();
```

### 4. Add Context with append()

Use `append()` to add metadata:

```php
$listings = Listing::allByShop($shopId);

$listings->append([
    'shop_id' => $shopId,
    'fetched_at' => date('Y-m-d H:i:s')
]);
```

### 5. Be Aware of API Limits

Etsy has rate limits. Don't make excessive requests:

```php
// Bad - makes many requests
foreach ($shopIds as $shopId) {
    $listings = Listing::allByShop($shopId);
    foreach ($listings->paginate(500) as $listing) {
        // ...
    }
}

// Better - batch operations, add delays
foreach ($shopIds as $shopId) {
    $listings = Listing::allByShop($shopId, ['limit' => 100]);
    processListings($listings);
    sleep(1);  // Rate limiting
}
```

### 6. Use count() vs count Property Correctly

```php
$listings = Listing::all();

// Total available from API
$totalOnEtsy = $listings->count;

// In current collection
$inThisPage = $listings->count();
```

### 7. Process Resources Efficiently

```php
// Good - memory efficient
foreach ($reviews->paginate(500) as $review) {
    processAndSave($review);
    // $review is garbage collected after each iteration
}

// Bad - loads all into memory
$allReviews = iterator_to_array($reviews->paginate(500));
foreach ($allReviews as $review) {
    processAndSave($review);
}
```

## Examples

### Example 1: Process All Shop Reviews

```php
use Etsy\Resources\Review;

$reviews = Review::all($shopId, ['limit' => 25]);

$totalProcessed = 0;
$ratingSum = 0;

foreach ($reviews->paginate(500) as $review) {
    $totalProcessed++;
    $ratingSum += $review->rating;
    
    // Save to database
    saveReviewToDatabase($review);
}

$averageRating = $ratingSum / $totalProcessed;
echo "Average rating: {$averageRating}\n";
echo "Total reviews processed: {$totalProcessed}\n";
```

### Example 2: Export Listings to CSV

```php
use Etsy\Resources\Listing;

$listings = Listing::allByShop($shopId, ['limit' => 100]);

$file = fopen('listings.csv', 'w');
fputcsv($file, ['ID', 'Title', 'Price', 'Quantity', 'State']);

foreach ($listings->paginate(500) as $listing) {
    fputcsv($file, [
        $listing->listing_id,
        $listing->title,
        $listing->price->amount / 100,  // Convert cents to dollars
        $listing->quantity,
        $listing->state
    ]);
}

fclose($file);
echo "Exported listings to listings.csv\n";
```

### Example 3: Find Listings by Criteria

```php
use Etsy\Resources\Listing;

$listings = Listing::allByShop($shopId, ['limit' => 100]);

$lowStockListings = [];

foreach ($listings->paginate(500) as $listing) {
    if ($listing->quantity < 5) {
        $lowStockListings[] = $listing;
    }
}

echo "Found " . count($lowStockListings) . " low stock listings\n";

foreach ($lowStockListings as $listing) {
    echo "- {$listing->title}: {$listing->quantity} remaining\n";
}
```

### Example 4: Batch Update Listings

```php
use Etsy\Resources\Listing;

$listings = Listing::allByShop($shopId, ['limit' => 100]);

$updated = 0;

foreach ($listings->paginate(200) as $listing) {
    // Only update if needed
    if (strpos($listing->title, '[SALE]') === false) {
        $listing->title = '[SALE] ' . $listing->title;
        $listing->save();
        $updated++;
        
        // Rate limiting
        usleep(500000);  // 0.5 second delay
    }
}

echo "Updated {$updated} listings\n";
```

### Example 5: Aggregate Shop Statistics

```php
use Etsy\Resources\Listing;

$listings = Listing::allByShop($shopId, ['limit' => 100]);

$stats = [
    'total' => 0,
    'active' => 0,
    'draft' => 0,
    'total_quantity' => 0,
    'total_value' => 0
];

foreach ($listings->paginate(500) as $listing) {
    $stats['total']++;
    $stats['total_quantity'] += $listing->quantity;
    $stats['total_value'] += ($listing->price->amount / 100) * $listing->quantity;
    
    if ($listing->state === 'active') {
        $stats['active']++;
    } elseif ($listing->state === 'draft') {
        $stats['draft']++;
    }
}

echo "Shop Statistics:\n";
echo "Total Listings: {$stats['total']}\n";
echo "Active: {$stats['active']}\n";
echo "Draft: {$stats['draft']}\n";
echo "Total Inventory: {$stats['total_quantity']} items\n";
echo "Total Inventory Value: $" . number_format($stats['total_value'], 2) . "\n";
```

### Example 6: Using append() for Context

```php
use Etsy\Resources\Receipt;

function getShopOrders($shopId, $dateRange) {
    $receipts = Receipt::all($shopId, [
        'min_created' => $dateRange['start'],
        'max_created' => $dateRange['end'],
        'limit' => 100
    ]);
    
    // Add context to each receipt
    $receipts->append([
        'shop_id' => $shopId,
        'report_date' => date('Y-m-d'),
        'date_range' => $dateRange
    ]);
    
    return $receipts;
}

$orders = getShopOrders($shopId, [
    'start' => strtotime('-30 days'),
    'end' => time()
]);

// Each receipt now has the context data
foreach ($orders->data as $receipt) {
    echo "Receipt {$receipt->receipt_id} for shop {$receipt->shop_id}\n";
    echo "Report date: {$receipt->report_date}\n";
}
```

### Example 7: Converting to Different Formats

```php
use Etsy\Resources\Listing;

$listings = Listing::allByShop($shopId, ['limit' => 50]);

// To JSON array
$jsonArray = $listings->toJson();
file_put_contents('listings.json', json_encode($jsonArray));

// To array of arrays
$arrays = array_map(function($listing) {
    return $listing->toArray();
}, $listings->data);

// To XML (custom)
$xml = new SimpleXMLElement('<listings/>');
foreach ($listings->data as $listing) {
    $item = $xml->addChild('listing');
    $item->addChild('id', $listing->listing_id);
    $item->addChild('title', htmlspecialchars($listing->title));
    $item->addChild('price', $listing->price->amount / 100);
}
file_put_contents('listings.xml', $xml->asXML());
```

## Summary

Collections in the Etsy PHP SDK provide:

1. **Structured Access**: Organized way to work with multiple resources
2. **Pagination Support**: Automatic multi-page fetching for large datasets
3. **Convenience Methods**: first(), count(), append(), toJson()
4. **Memory Efficiency**: Generator-based pagination
5. **Flexibility**: Direct array access or method-based access

Key points to remember:

- Check if collection has data before accessing
- Use pagination for large datasets (max 500 results)
- Only Listing, Shop, and Review support pagination
- Use `append()` to add context data
- `count` property ≠ `count()` method
- Generators are memory-efficient for pagination
- Always handle potential empty collections

For more information, see:
- [API Reference](API_REFERENCE.md) - Detailed resource methods
- [Architecture](ARCHITECTURE.md) - How collections work internally
- [Examples](EXAMPLES.md) - More real-world usage patterns
