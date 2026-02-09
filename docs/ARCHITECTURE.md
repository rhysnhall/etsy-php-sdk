# Architecture

This document provides a deep dive into the Etsy PHP SDK architecture, design patterns, and internal workings.

## Table of Contents

- [Core Components](#core-components)
- [Design Patterns](#design-patterns)
- [Request Flow](#request-flow)
- [Class Hierarchy](#class-hierarchy)
- [Error Handling](#error-handling)
- [Internal Mechanisms](#internal-mechanisms)

## Core Components

### 1. Etsy Class

**Location**: `src/Etsy.php`

The main entry point for the SDK. Initializes the OAuth client and provides utilities for resource creation.

```php
namespace Etsy;

class Etsy
{
    public static $client;  // OAuth\Client instance
    
    public function __construct(
        string $client_id,
        string $shared_secret,
        ?string $api_key = null,
        array $config = []
    );
}
```

**Responsibilities**:
- Initialize the OAuth client
- Manage API authentication
- Provide factory methods for creating Resources and Collections
- Expose the HTTP client for direct API requests

**Key Methods**:
- `getResource($response, string $resource)`: Converts API responses to Resource or Collection objects
- `createResource($record, string $resource)`: Creates a single Resource instance
- `createCollection($response, string $resource)`: Creates a Collection of Resources
- `scopes()`: Check permission scopes for the current user

### 2. Resource Class

**Location**: `src/Resource.php`

Base class for all Etsy API resources (Listing, Shop, User, etc.).

```php
namespace Etsy;

abstract class Resource
{
    protected $_associations = [];    // Related resources
    protected $_rename = [];          // Property renaming map
    protected $_saveable = [];        // Properties that can be saved
    protected $_properties = [];      // Actual resource data
    private $_originalState = [];     // Tracks original values for change detection
}
```

**Responsibilities**:
- Store and manage resource data
- Handle property access and mutation
- Manage associations (relationships) with other resources
- Provide save/update functionality with change tracking
- Convert resources to arrays/JSON

**Key Features**:

#### Magic Methods
- `__get($property)`: Access resource properties (case-insensitive)
- `__set($property, $value)`: Set resource properties

#### Associations
Automatically converts associated data to appropriate Resource objects:

```php
protected $_associations = [
    "shop" => "Shop",
    "user" => "User",
    "images" => "ListingImage"
];

// When accessing $listing->shop, returns a Shop instance, not raw data
$shop = $listing->shop;  // Instance of Etsy\Resources\Shop
```

#### Change Tracking
The `save()` method uses `_originalState` to detect changes:

```php
$listing = Listing::get($listingId);
$listing->title = "New Title";
$listing->description = "New Description";

// Only sends 'title' and 'description' in the PATCH request
$listing->save();
```

#### Saveable Properties
The `_saveable` array defines which properties can be updated:

```php
protected $_saveable = [
    'title',
    'description',
    'price',
    // ... only these can be saved
];
```

### 3. Collection Class

**Location**: `src/Collection.php`

Container for multiple Resource instances with pagination support.

```php
namespace Etsy;

class Collection
{
    public $data = [];        // Array of Resource objects
    public $count = 0;        // Total count from API
    protected $resource;      // Resource type name
    protected $uri;           // API endpoint
    protected $params = [];   // Query parameters
}
```

**Responsibilities**:
- Hold multiple Resource objects
- Provide pagination for large result sets
- Offer convenience methods for collection manipulation
- Maintain API endpoint context for pagination

**Key Methods**:
- `first()`: Get the first resource
- `count()`: Count resources in collection
- `append(array $data)`: Add properties to all resources
- `paginate(int $results)`: Generator for fetching multiple pages
- `toJson()`: Convert all resources to JSON

**Pagination Support**:
Only these resources support pagination:
- Shop
- Review
- Listing

### 4. OAuth Client

**Location**: `src/OAuth/Client.php`

Handles OAuth 2.0 authentication and HTTP requests to the Etsy API.

```php
namespace Etsy\OAuth;

class Client
{
    const CONNECT_URL = "https://www.etsy.com/oauth/connect";
    const TOKEN_URL = "https://api.etsy.com/v3/public/oauth/token";
    const API_URL = "https://api.etsy.com/v3";
    
    protected $client_id;
    protected $headers = [];
    protected $config = [];
}
```

**Responsibilities**:
- Generate OAuth 2.0 authorization URLs
- Exchange authorization codes for access tokens
- Refresh expired tokens
- Make HTTP requests (GET, POST, PUT, PATCH, DELETE)
- Handle API errors and responses

**Authentication Methods**:
- `getAuthorizationUrl()`: Generate OAuth URL for user authorization
- `requestAccessToken()`: Exchange code for access token
- `refreshAccessToken()`: Refresh an expired token
- `exchangeLegacyToken()`: Convert OAuth 1.0 token to OAuth 2.0

**HTTP Methods** (via `__call`):
- `get(string $uri, array $params)`: GET request
- `post(string $uri, array $data)`: POST request
- `put(string $uri, array $data)`: PUT request
- `patch(string $uri, array $data)`: PATCH request
- `delete(string $uri, array $params)`: DELETE request

## Design Patterns

### 1. Active Record Pattern

Resources follow an Active Record-like pattern where each instance represents a database record:

```php
// Fetch a resource
$listing = Listing::get(123456);

// Modify it
$listing->title = "Updated Title";

// Save changes
$listing->save();
```

### 2. Static Factory Methods

Resources use static methods for creation and retrieval:

```php
// Instead of: new Listing(...)
// Use:
$listing = Listing::get($listingId);
$listings = Listing::all();
$newListing = Listing::create($shopId, $data);
```

### 3. Association Pattern

Resources automatically resolve associations:

```php
$listing = Listing::get($listingId);

// Automatic resolution - returns Shop instance
$shop = $listing->shop;

// Automatic resolution - returns array of ListingImage instances
$images = $listing->images;
```

### 4. Repository Pattern (Partial)

Collections act as repositories:

```php
$listings = Shop::listings($shopId);
$firstListing = $listings->first();
$totalCount = $listings->count();
```

### 5. Fluent Interface

Method chaining for collections:

```php
$reviews = Review::all()
    ->append(['custom_field' => 'value'])
    ->toJson();
```

## Request Flow

### Standard GET Request Flow

```
1. User Code
   └─> Listing::get($listingId)

2. Resource::request() [Static Method]
   └─> Constructs API URL
   └─> Calls Etsy::$client->get($url, $params)

3. OAuth\Client::get() [Magic Method via __call]
   └─> Builds full URL with API base
   └─> Creates GuzzleHttp\Client
   └─> Executes HTTP GET request
   └─> Handles exceptions
   └─> Returns JSON decoded response

4. Resource::request() continues
   └─> Calls Etsy::getResource($response, 'Listing')

5. Etsy::getResource()
   └─> Detects single resource (no 'results' key)
   └─> Calls Etsy::createResource($response, 'Listing')
   └─> Creates new Listing instance

6. Resource::__construct()
   └─> Stores properties
   └─> Saves original state
   └─> Resolves associations
   └─> Renames properties if needed

7. Return to User Code
   └─> Receives Listing instance
```

### Collection Request Flow

```
1. User Code
   └─> Listing::all()

2-3. [Same as above]

4. Resource::request() continues
   └─> Calls Etsy::getResource($response, 'Listing')

5. Etsy::getResource()
   └─> Detects collection (has 'results' key)
   └─> Calls Etsy::createCollection($response, 'Listing')

6. Etsy::createCollection()
   └─> Creates new Collection instance
   └─> Sets count property
   └─> Calls createCollectionResources() for each result
   └─> Each result becomes a Listing instance

7. Return to User Code
   └─> Receives Collection instance with Listing objects in $data
```

### Save Request Flow

```
1. User Code
   └─> $listing->title = "New Title"
   └─> $listing->save()

2. Resource::save() [Instance Method]
   └─> Calls getSaveData(true)
   └─> Compares current properties to _originalState
   └─> Filters by _saveable properties
   └─> Returns only changed, saveable properties

3. Resource::updateRequest()
   └─> Builds PATCH URL
   └─> Calls Resource::request('PATCH', $url, 'Listing', $data)

4-6. [Same as GET request flow]

7. Resource::updateRequest() continues
   └─> Updates current instance properties with response
   └─> Updates _originalState
   └─> Returns $this for method chaining

8. Return to User Code
   └─> Receives updated Listing instance
```

## Class Hierarchy

```
Etsy (Main Entry Point)
└── OAuth\Client (HTTP & Auth)

Resource (Base Class)
├── Listing
│   └── Uses: ListingImage, ListingVideo, ListingFile, etc.
├── Shop
│   └── Uses: ShippingProfile, ReturnPolicy, ShopSection, etc.
├── User
├── Receipt
│   └── Uses: Transaction, Payment
├── Review
├── Transaction
├── Payment
├── ShippingProfile
│   └── Uses: ShippingDestination, ShippingUpgrade
├── ShippingDestination
├── ShippingUpgrade
├── ShippingCarrier
├── ReturnPolicy
├── ProductionPartner
├── ProcessingProfile
├── ShopSection
├── ListingImage
├── ListingVideo
├── ListingFile
├── ListingInventory
│   └── Uses: ListingProduct, ListingOffering
├── ListingProduct
│   └── Uses: ListingOffering
├── ListingOffering
├── ListingProperty
├── ListingTranslation
├── ListingVariationImage
├── LedgerEntry
├── Shipment
├── UserAddress
├── HolidayPreference
├── SellerTaxonomy
│   └── Uses: SellerTaxonomyProperty
├── SellerTaxonomyProperty
├── BuyerTaxonomy
│   └── Uses: BuyerTaxonomyProperty
└── BuyerTaxonomyProperty

Collection
└── Contains array of any Resource type

Utils
├── PermissionScopes (OAuth scope handling)
├── Request (HTTP request utilities)
└── Date (Date utilities)

Exceptions
├── SdkException (Base SDK exception)
├── ApiException (API errors)
├── OAuthException (OAuth errors)
└── RequestException (HTTP request errors)
```

## Error Handling

### Exception Hierarchy

```php
\Exception
└── Etsy\Exception\SdkException (Base)
    ├── Etsy\Exception\ApiException
    ├── Etsy\Exception\OAuthException
    └── Etsy\Exception\RequestException
```

### When Exceptions Are Thrown

1. **OAuthException**
   - Invalid client ID or shared secret
   - Failed access token request
   - Failed refresh token request

2. **RequestException**
   - Invalid HTTP method
   - Missing URI parameter
   - HTTP errors (if 404_error config is true)
   - Non-404 HTTP errors (always thrown)

3. **SdkException**
   - Pagination on unsupported resources
   - General SDK logic errors

### 404 Handling

By default, 404 responses return `null` instead of throwing exceptions:

```php
$listing = Listing::get(999999);  // Returns null if not found

// To throw exceptions on 404:
$etsy = new Etsy($clientId, $sharedSecret, $accessToken, [
    '404_error' => true
]);
```

### Error Response Structure

When requests fail, the Client returns an object with error details:

```php
stdClass {
    uri: "/application/listings/123",
    error: "Listing not found",
    code: 404
}
```

## Internal Mechanisms

### Property Case Insensitivity

Resource property access is case-insensitive:

```php
$listing->shop_id === $listing->Shop_Id === $listing->SHOP_ID
```

This is handled in `Resource::__get()` by converting all property names to lowercase during comparison.

### Property Renaming

Some Etsy API responses have inconsistent naming. The `_rename` array fixes this:

```php
protected $_rename = [
    'old_name' => 'new_name'
];
```

### Automatic Shop ID Assignment

Some resources need the `shop_id` for their instance methods:

```php
// In Listing resource
private function assignShopIdToIncludedResources() {
    if(isset($this->_properties->images)) {
        foreach($this->_properties->images as $image) {
            $image->shop_id = $this->shop_id;
        }
    }
}
```

### File Upload Detection

The `Request::prepareFile()` utility detects file uploads by checking for `image`, `file`, or `video` keys in request data and converts them to multipart format for GuzzleHttp.

### Pagination State

Collections maintain pagination state internally:

```php
protected $uri;      // Original API endpoint
protected $params;   // Original query parameters
```

When paginating, it increments the `offset` parameter and makes new requests while preserving all other parameters.

### Change Detection Algorithm

The `getChanged()` method recursively compares arrays:

```php
private function getChanged(array $arrayOne, array $arrayTwo): array
{
    $changed = [];
    foreach($arrayOne as $key => $value) {
        if(array_key_exists($key, $arrayTwo)) {
            if(is_array($value)) {
                // Recursive comparison for nested arrays
                $recursiveChanged = self::getChanged($arrayTwo[$key], $value);
                if(count($recursiveChanged)) {
                    $changed[$key] = $recursiveChanged;
                }
            }
            else {
                // Simple comparison for scalar values
                if($value != $arrayTwo[$key]) {
                    $changed[$key] = $value;
                }
            }
        }
        else {
            // New property added
            $changed[$key] = $value;
        }
    }
    return $changed;
}
```

This ensures only modified properties are sent in PATCH requests, reducing payload size and preventing accidental overwrites.

## Best Practices

### 1. Always Initialize Etsy Before Using Resources

```php
// Required before any resource calls
$etsy = new Etsy($clientId, $sharedSecret, $accessToken);

// Now resources can be used
$user = User::me();
```

### 2. Use Instance Methods When Available

```php
// Good - uses instance method
$listing = Listing::get($listingId);
$images = $listing->images();

// Also works but less convenient
$images = ListingImage::all($listingId);
```

### 3. Leverage Change Tracking

```php
$listing = Listing::get($listingId);
$listing->title = "New Title";

// Only sends changed properties
$listing->save();
```

### 4. Handle Null Returns

```php
$listing = Listing::get($invalidId);
if (!$listing) {
    // Handle not found
}
```

### 5. Use Collections Efficiently

```php
// Good - use pagination for large datasets
foreach($listings->paginate(200) as $listing) {
    // Process each listing
}

// Avoid - loading all at once
$allListings = Listing::all(['limit' => 1000]);  // Bad for large datasets
```

## Summary

The Etsy PHP SDK provides a well-structured, object-oriented interface to the Etsy API v3. Its design prioritizes:

- **Developer experience** through intuitive method names and patterns
- **Efficiency** with change tracking and selective updates
- **Type safety** with Resource and Collection objects
- **Flexibility** with direct client access for advanced use cases
- **Maintainability** with clear separation of concerns

Understanding these architectural decisions will help you use the SDK effectively and extend it if needed.
