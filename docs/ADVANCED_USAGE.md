# Advanced Usage

Advanced features and techniques for using the Etsy PHP SDK.

## Table of Contents

- [Direct API Requests](#direct-api-requests)
- [Resource Conversion](#resource-conversion)
- [Configuration Options](#configuration-options)
- [Custom Request Headers](#custom-request-headers)
- [Change Tracking](#change-tracking)
- [Associations](#associations)
- [Error Handling](#error-handling)
- [Performance Optimization](#performance-optimization)

## Direct API Requests

For endpoints not yet covered by the SDK or custom API calls, use the HTTP client directly.

### Accessing the HTTP Client

After initializing Etsy, the HTTP client is available:

```php
use Etsy\Etsy;

$etsy = new Etsy($clientId, $sharedSecret, $accessToken);

// Access the client
$client = Etsy::$client;
```

### Making GET Requests

```php
$response = Etsy::$client->get(
    "/application/listings/active",
    [
        "limit" => 25,
        "keywords" => "handmade"
    ]
);

// $response is a stdClass object
print_r($response);
```

**Parameters**:
- **$uri** (string): API endpoint path (without base URL)
- **$params** (array): Query parameters

**Returns**: stdClass with API response

### Making POST Requests

```php
$response = Etsy::$client->post(
    "/application/shops/{$shopId}/listings",
    [
        "quantity" => 10,
        "title" => "New Product",
        "description" => "Product description",
        "price" => 29.99,
        // ... other required fields
    ]
);
```

### Making PUT Requests

```php
$response = Etsy::$client->put(
    "/application/shops/{$shopId}",
    [
        "title" => "Updated Shop Title"
    ]
);
```

### Making PATCH Requests

```php
$response = Etsy::$client->patch(
    "/application/shops/{$shopId}/listings/{$listingId}",
    [
        "title" => "Updated Title",
        "price" => 34.99
    ]
);
```

### Making DELETE Requests

```php
$response = Etsy::$client->delete(
    "/application/listings/{$listingId}",
    []  // Optional query parameters
);
```

### Complete Example

```php
use Etsy\Etsy;

$etsy = new Etsy($clientId, $sharedSecret, $accessToken);

// Custom endpoint not in SDK
$response = Etsy::$client->get(
    "/application/shops/{$shopId}/stats",
    [
        "start_date" => "2024-01-01",
        "end_date" => "2024-12-31"
    ]
);

if (!isset($response->error)) {
    echo "Views: {$response->views}\n";
    echo "Favorites: {$response->favorites}\n";
} else {
    echo "Error: {$response->error}\n";
}
```

## Resource Conversion

Convert raw API responses into Resource or Collection objects.

### getResource() Method

```php
use Etsy\Etsy;

$etsy = new Etsy($clientId, $sharedSecret, $accessToken);

// Make raw API request
$response = Etsy::$client->get("/application/shops/{$shopId}");

// Convert to Resource
$shop = Etsy::getResource($response, 'Shop');

// Now use as a Shop resource
echo "Shop: {$shop->shop_name}\n";
echo "URL: {$shop->url}\n";
```

**Parameters**:
- **$response** (object): Raw API response
- **$resource** (string): Resource class name (without namespace)

**Returns**: Resource instance or Collection

### Converting Collections

```php
// Request that returns multiple results
$response = Etsy::$client->get(
    "/application/listings/active",
    ["limit" => 25]
);

// Convert to Collection
$listings = Etsy::getResource($response, 'Listing');

// Now use as Collection
foreach ($listings->data as $listing) {
    echo $listing->title . "\n";
}
```

### Detecting Response Type

The SDK automatically detects whether the response is a single resource or collection:

```php
// Single resource (no 'results' key in response)
$shop = Etsy::getResource($response, 'Shop');  // Returns Shop instance

// Collection (has 'results' key in response)
$listings = Etsy::getResource($response, 'Listing');  // Returns Collection
```

### When to Use

Use `getResource()` when:
1. The SDK doesn't have a method for the endpoint
2. You need to intercept/modify the request
3. You want to build custom wrappers
4. You're testing or debugging

```php
// Example: Custom search with special parameters
function customSearch($keyword, $customFilter) {
    $response = Etsy::$client->get(
        "/application/listings/active",
        [
            "keywords" => $keyword,
            "custom_filter" => $customFilter  // Hypothetical parameter
        ]
    );
    
    return Etsy::getResource($response, 'Listing');
}
```

## Configuration Options

Configure SDK behavior when initializing.

### 404 Error Handling

By default, 404 responses return `null` instead of throwing exceptions:

```php
$listing = Listing::get(999999);  // Returns null if not found

if (!$listing) {
    echo "Listing not found";
}
```

**Enable 404 Exceptions**:

```php
$etsy = new Etsy(
    $clientId,
    $sharedSecret,
    $accessToken,
    ['404_error' => true]  // Throw exceptions on 404
);

try {
    $listing = Listing::get(999999);
} catch (\Etsy\Exception\RequestException $e) {
    echo "Error: " . $e->getMessage();
}
```

### Configuration Array

```php
$config = [
    '404_error' => true,  // Throw exceptions on 404
    // Future config options will go here
];

$etsy = new Etsy($clientId, $sharedSecret, $accessToken, $config);
```

### When to Enable 404 Errors

**Enable** when:
- You want to catch all errors uniformly
- Missing resources should halt execution
- You prefer exceptions over null checks

**Keep Disabled** when:
- Missing resources are expected
- You prefer null checks
- You want simpler error handling

## Custom Request Headers

### Setting Headers via Client

Currently, the SDK manages headers internally. For custom headers, you'd need to extend the Client class or use Guzzle directly.

### Accessing Guzzle

The SDK uses GuzzleHttp internally:

```php
use Etsy\OAuth\Client;

class CustomClient extends Client {
    public function customRequest($uri, $headers = []) {
        $client = $this->createHttpClient();
        
        $response = $client->get(
            self::API_URL . $uri,
            [
                'headers' => array_merge($this->headers, $headers)
            ]
        );
        
        return json_decode($response->getBody(), false);
    }
}
```

## Change Tracking

The SDK tracks changes to resources and only sends modified data in save operations.

### How It Works

```php
$listing = Listing::get($listingId);

// Original state is stored internally
// _originalState = ['title' => 'Old Title', 'price' => [...], ...]

$listing->title = "New Title";
$listing->description = "New Description";

// save() compares current vs original
$listing->save();

// Only sends: {title: "New Title", description: "New Description"}
```

### Manual Save with Specific Data

Override change tracking by providing data:

```php
$listing = Listing::get($listingId);

// Ignores change tracking, sends only this
$listing->save([
    'title' => 'Specific Title',
    'tags' => ['new', 'tags']
]);
```

### Saveable Properties

Not all properties can be saved. Each resource defines `$_saveable`:

```php
// In Listing class
protected $_saveable = [
    'title',
    'description',
    'price',
    'tags',
    // ... only these can be updated
];
```

Trying to save non-saveable properties won't cause errors - they're simply ignored.

### Viewing Original State

The original state is private, but you can compare:

```php
$listing = Listing::get($listingId);

$originalData = $listing->toArray();

$listing->title = "New Title";

// Check what changed
$currentData = $listing->toArray();

$changes = array_diff_assoc($currentData, $originalData);
print_r($changes);
```

### Bypassing Change Tracking

Use static update methods to send exact data:

```php
// With change tracking (instance method)
$listing->title = "New";
$listing->save();  // Sends only changed fields

// Without change tracking (static method)
Listing::update($shopId, $listingId, [
    'title' => 'New',
    'price' => 29.99
]);  // Sends exactly what you specify
```

## Associations

Resources automatically resolve relationships to other resources.

### Defining Associations

In resource classes:

```php
// In Listing class
protected $_associations = [
    "shop" => "Shop",
    "user" => "User",
    "images" => "ListingImage",
    'shipping_profile' => 'ShippingProfile',
    'videos' => 'ListingVideo'
];
```

### Single Associations

When the API returns associated data, it's converted to the appropriate resource:

```php
$listing = Listing::get($listingId, [
    'includes' => ['Shop', 'User']
]);

// shop property is a Shop resource instance
$shop = $listing->shop;
echo $shop->shop_name;

// user property is a User resource instance
$user = $listing->user;
echo $user->first_name;
```

### Collection Associations

Arrays are converted to collections of resources:

```php
$listing = Listing::get($listingId, [
    'includes' => ['Images']
]);

// images property is an array of ListingImage resources
foreach ($listing->images as $image) {
    echo $image->url_570xN . "\n";
}
```

### Null Associations

If associated data isn't included, properties return `null`:

```php
$listing = Listing::get($listingId);  // No includes

$shop = $listing->shop;  // null (not included in response)
```

### Lazy Loading vs Eager Loading

The SDK doesn't implement lazy loading. Associations must be included in the initial request:

```php
// Eager loading - includes shop data
$listing = Listing::get($listingId, ['includes' => ['Shop']]);
$shop = $listing->shop;  // Shop resource

// No lazy loading - shop not included
$listing = Listing::get($listingId);
$shop = $listing->shop;  // null

// Manually fetch if needed
if (!$shop) {
    $shop = Shop::get($listing->shop_id);
}
```

### Custom Associations

For resources with instance methods:

```php
$user = User::get();

// Instance method fetches related shop
$shop = $user->shop();  // Makes API call

// vs

$listing = Listing::get($listingId, ['includes' => ['Shop']]);
$shop = $listing->shop;  // No API call, already included
```

## Error Handling

### Exception Hierarchy

```php
\Exception
└── Etsy\Exception\SdkException
    ├── Etsy\Exception\ApiException
    ├── Etsy\Exception\OAuthException
    └── Etsy\Exception\RequestException
```

### Catching Specific Exceptions

```php
use Etsy\Exception\{OAuthException, RequestException, SdkException};

try {
    $listing = Listing::get($listingId);
} catch (RequestException $e) {
    // HTTP/API errors
    echo "API error: " . $e->getMessage();
} catch (SdkException $e) {
    // SDK logic errors
    echo "SDK error: " . $e->getMessage();
}
```

### OAuth-Specific Errors

```php
use Etsy\OAuth\Client;
use Etsy\Exception\OAuthException;

$client = new Client($clientId, $sharedSecret);

try {
    [$accessToken, $refreshToken] = $client->requestAccessToken(
        $redirectUri,
        $code,
        $verifier
    );
} catch (OAuthException $e) {
    // OAuth flow errors
    echo "OAuth error: " . $e->getMessage();
    // Re-initiate authorization
}
```

### Comprehensive Error Handling

```php
use Etsy\Exception\{OAuthException, RequestException, SdkException};

function safeApiCall(callable $callback, $retries = 3) {
    $attempt = 0;
    
    while ($attempt < $retries) {
        try {
            return $callback();
        } catch (OAuthException $e) {
            // Don't retry OAuth errors
            throw $e;
        } catch (RequestException $e) {
            // Check if retryable (e.g., 5xx errors)
            if (strpos($e->getMessage(), '5') === false || $attempt >= $retries - 1) {
                throw $e;
            }
            $attempt++;
            sleep(pow(2, $attempt));  // Exponential backoff
        } catch (SdkException $e) {
            // SDK logic errors shouldn't be retried
            throw $e;
        }
    }
}

// Usage
$listing = safeApiCall(function() use ($listingId) {
    return Listing::get($listingId);
}, 3);
```

### Error Response Structure

When errors occur, check the response:

```php
$response = Etsy::$client->get("/application/listings/{$invalidId}");

if (isset($response->error)) {
    echo "Error: {$response->error}\n";
    echo "Code: {$response->code}\n";
}
```

## Performance Optimization

### 1. Use Includes Wisely

Only include associations you need:

```php
// Bad - includes everything
$listing = Listing::get($listingId, [
    'includes' => ['Shop', 'User', 'Images', 'Videos', 'Shipping']
]);

// Good - only includes what you need
$listing = Listing::get($listingId, [
    'includes' => ['Shop']
]);
```

### 2. Batch Requests

Fetch multiple resources in one call when possible:

```php
// Bad - 100 separate requests
foreach ($listingIds as $id) {
    $listing = Listing::get($id);
    process($listing);
}

// Good - 1 request for up to 100 IDs
$listings = Listing::allByIds($listingIds);
foreach ($listings->data as $listing) {
    process($listing);
}
```

### 3. Cache Results

```php
// Simple file-based cache
function getCachedShop($shopId, $ttl = 3600) {
    $cacheFile = "./cache/shop_{$shopId}.json";
    
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
        $data = json_decode(file_get_contents($cacheFile));
        return Etsy::createResource($data, 'Shop');
    }
    
    $shop = Shop::get($shopId);
    file_put_contents($cacheFile, $shop->toJson());
    
    return $shop;
}
```

### 4. Limit Results

Don't fetch more than you need:

```php
// Bad
$allListings = Listing::all(['limit' => 100]);

// Good - if you only need 10
$listings = Listing::all(['limit' => 10]);
```

### 5. Use Pagination Efficiently

```php
// Process as you go (memory efficient)
foreach ($reviews->paginate(500) as $review) {
    processAndSave($review);
}

// Don't load all into array (memory inefficient)
$allReviews = iterator_to_array($reviews->paginate(500));
```

### 6. Connection Reuse

The SDK reuses the HTTP client automatically, but for multiple Etsy instances:

```php
// Bad - creates multiple clients
foreach ($shops as $shop) {
    $etsy = new Etsy($clientId, $sharedSecret, $accessToken);
    // ...
}

// Good - reuse instance
$etsy = new Etsy($clientId, $sharedSecret, $accessToken);
foreach ($shops as $shop) {
    // ... use same $etsy instance
}
```

### 7. Async/Parallel Requests

For truly parallel requests, use Guzzle's async capabilities:

```php
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

$client = new Client();

$promises = [];
foreach ($listingIds as $id) {
    $promises[] = $client->getAsync("https://api.etsy.com/v3/application/listings/{$id}", [
        'headers' => ['Authorization' => "Bearer {$accessToken}"]
    ]);
}

$responses = Promise\unwrap($promises);
```

**Note**: This is advanced usage outside the SDK's built-in methods.

## Summary

Advanced features covered:

1. **Direct API Requests**: Access any endpoint via `Etsy::$client`
2. **Resource Conversion**: Convert raw responses with `Etsy::getResource()`
3. **Configuration**: Control error handling with config options
4. **Change Tracking**: Automatic detection of modified properties
5. **Associations**: Automatic relationship resolution
6. **Error Handling**: Comprehensive exception catching
7. **Performance**: Optimization techniques for efficiency

These advanced features give you full control over the SDK while maintaining the convenience of the high-level API.

For more information, see:
- [Architecture](ARCHITECTURE.md) - How these features work internally
- [API Reference](API_REFERENCE.md) - Complete method documentation
- [Examples](EXAMPLES.md) - Practical implementations
