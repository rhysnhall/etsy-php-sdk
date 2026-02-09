# Etsy PHP SDK

A modern, developer-friendly PHP SDK for the Etsy API v3 with full OAuth 2.0 support.

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.0-blue.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE.md)
[![Version](https://img.shields.io/badge/version-1.2.0-orange.svg)](CHANGELOG.md)

## Quick Links

- **[Installation](#installation)** - Get started quickly
- **[Authentication Guide](docs/AUTHENTICATION.md)** - Complete OAuth 2.0 setup
- **[API Reference](docs/API_REFERENCE.md)** - All 32 resources documented
- **[Examples](docs/EXAMPLES.md)** - Real-world code samples
- **[Troubleshooting](docs/TROUBLESHOOTING.md)** - Common issues and solutions

## Features

- ✅ **Full Etsy API v3 Support** - All endpoints and resources
- ✅ **OAuth 2.0 with PKCE** - Secure authentication
- ✅ **Resource-Based Architecture** - Intuitive object-oriented interface
- ✅ **Change Tracking** - Automatic detection of modified properties
- ✅ **Pagination Support** - Handle large datasets efficiently
- ✅ **File Uploads** - Images, videos, and downloadable files
- ✅ **Type Safety** - Clear return types and documentation
- ✅ **Association Resolution** - Automatic relationship loading

## Requirements

- **PHP**: 8.0 or greater
- **Composer**: For package management
- **GuzzleHTTP**: ^7.3 (installed automatically)

## Installation

Install via Composer:

```bash
composer require rhysnhall/etsy-php-sdk
```

## Quick Start

### 1. Initialize the SDK

```php
use Etsy\Etsy;

$etsy = new Etsy(
    $client_id,      // Your app's client ID
    $shared_secret,  // Your app's shared secret
    $access_token    // User's access token
);
```

### 2. Make Your First Request

```php
use Etsy\Resources\User;

// Get authenticated user
$user = User::me();
echo "Hello, {$user->first_name}!\n";

// Get user's shop
$shop = $user->shop();
echo "Shop: {$shop->shop_name}\n";
```

### 3. Work with Resources

```php
use Etsy\Resources\Listing;

// Get a listing
$listing = Listing::get($listingId);
echo "Title: {$listing->title}\n";
echo "Price: \${$listing->price->amount / 100}\n";

// Update listing
$listing->title = "Updated Title";
$listing->save();

// Get shop listings
$listings = Listing::allByShop($shopId, ['limit' => 25]);
foreach ($listings->data as $listing) {
    echo "{$listing->title}\n";
}
```

## Authentication

The SDK uses **OAuth 2.0 with PKCE** for authentication. 

### Quick OAuth Setup

1. Register your app at [`developers.etsy.com/register`](https://www.etsy.com/developers/register)
2. Get your Client ID and Shared Secret
3. Implement the OAuth flow:

```php
use Etsy\OAuth\Client;

// Step 1: Initialize OAuth client
$client = new Client($clientId, $sharedSecret);

// Step 2: Generate PKCE code
[$verifier, $codeChallenge] = $client->generateChallengeCode();
$_SESSION['pkce_verifier'] = $verifier;

// Step 3: Generate nonce for CSRF protection
$nonce = $client->createNonce();
$_SESSION['oauth_nonce'] = $nonce;

// Step 4: Redirect user to Etsy
$authUrl = $client->getAuthorizationUrl(
    $redirectUri,
    ['listings_r', 'listings_w', 'shops_r'],  // Required scopes
    $codeChallenge,
    $nonce
);
header("Location: {$authUrl}");
```

In your callback handler:

```php
// Step 5: Exchange code for tokens
[$accessToken, $refreshToken] = $client->requestAccessToken(
    $redirectUri,
    $_GET['code'],
    $_SESSION['pkce_verifier']
);

// Store tokens securely
$_SESSION['etsy_access_token'] = $accessToken;
$_SESSION['etsy_refresh_token'] = $refreshToken;
```

**📖 [Complete Authentication Guide](docs/AUTHENTICATION.md)** - OAuth 2.0, token management, scopes, security

## Core Concepts

### Resources

Resources represent Etsy entities (Listings, Shops, Users, etc.). Each resource provides static and instance methods:

```php
use Etsy\Resources\Listing;

// Static methods - fetch resources
$listing = Listing::get($listingId);
$listings = Listing::all(['limit' => 25]);
$shopListings = Listing::allByShop($shopId);

// Instance methods - work with a specific resource
$listing->save();                    // Save changes
$images = $listing->images();        // Get related resources
$listing->uploadImage($path, [...]);  // Upload file
```

**📖 [API Reference](docs/API_REFERENCE.md)** - All 32 resources with complete method documentation

### Collections

Multiple resources are returned as Collections with helpful methods:

```php
$listings = Listing::all();

// Access data
$firstListing = $listings->first();
$count = $listings->count();

// Pagination (for Listing, Shop, Review)
foreach ($listings->paginate(200) as $listing) {
    // Automatically fetches multiple pages
}

// Manipulation
$listings->append(['custom_field' => 'value']);
$jsonArray = $listings->toJson();
```

**📖 [Collections Guide](docs/COLLECTIONS.md)** - Working with collections, pagination, and bulk operations

### Change Tracking

The SDK automatically tracks changes and only sends modified data:

```php
$listing = Listing::get($listingId);

// Modify properties
$listing->title = "New Title";
$listing->description = "New Description";

// Only sends changed fields
$listing->save();
```

## Common Tasks

### Managing Listings

```php
use Etsy\Resources\Listing;

// Create listing
$listing = Listing::create($shopId, [
    'quantity' => 10,
    'title' => 'Handmade Ceramic Mug',
    'description' => 'Beautiful handcrafted...',
    'price' => 24.99,
    'who_made' => 'i_did',
    'when_made' => '2020_2023',
    'taxonomy_id' => 1234,
    'shipping_profile_id' => 5678,
    'tags' => ['ceramic', 'mug', 'handmade']
]);

// Upload images
$listing->uploadImage('./photo1.jpg', ['rank' => 1]);
$listing->uploadImage('./photo2.jpg', ['rank' => 2]);

// Update listing
$listing->title = "Updated Title";
$listing->save();

// Get listings
$allListings = Listing::allByShop($shopId);
$activeListings = Listing::allActiveByShop($shopId);
```

### Processing Orders

```php
use Etsy\Resources\Receipt;

// Get recent orders
$receipts = Receipt::all($shopId, [
    'min_created' => strtotime('-7 days'),
    'was_paid' => true,
    'limit' => 100
]);

foreach ($receipts->data as $receipt) {
    // Get items
    $transactions = $receipt->transactions();
    
    // Create shipment
    $receipt->shipment([
        'tracking_code' => 'TRACK123',
        'carrier_name' => 'USPS'
    ]);
    
    // Mark shipped
    $receipt->was_shipped = true;
    $receipt->save();
}
```

### Managing Inventory

```php
use Etsy\Resources\{Listing, ListingInventory};

// Update inventory
$inventory = ListingInventory::get($listingId);
// Modify inventory...
ListingInventory::update($listingId, $inventoryData);

// Low stock alert
$listings = Listing::allByShop($shopId);
foreach ($listings->data as $listing) {
    if ($listing->quantity < 5) {
        echo "Low stock: {$listing->title} ({$listing->quantity})\n";
    }
}
```

### Uploading Files

```php
// Images
$listing->uploadImage('./path/image.jpg', [
    'rank' => 1,
    'alt_text' => 'Product front view'
]);

// Videos
$listing->uploadVideo('./path/video.mp4', 'demo.mp4');

// Downloadable files
$listing->uploadFile('./path/file.pdf', 'Template.pdf');
```

**📖 [File Upload Guide](docs/FILE_UPLOADS.md)** - Images, videos, and downloadable files

## Documentation

### Complete Guides

- **[Authentication](docs/AUTHENTICATION.md)** - OAuth 2.0 setup, token management, scopes, security
- **[API Reference](docs/API_REFERENCE.md)** - All 32 resources with methods, parameters, examples
- **[Collections](docs/COLLECTIONS.md)** - Working with collections, pagination, bulk operations
- **[File Uploads](docs/FILE_UPLOADS.md)** - Images, videos, and downloadable files
- **[Advanced Usage](docs/ADVANCED_USAGE.md)** - Direct API requests, custom configurations, performance
- **[Examples](docs/EXAMPLES.md)** - Real-world scenarios and complete workflows
- **[Troubleshooting](docs/TROUBLESHOOTING.md)** - Common issues and solutions
- **[Migration Guide](docs/MIGRATION_GUIDE.md)** - Upgrading between versions
- **[Architecture](docs/ARCHITECTURE.md)** - How the SDK works internally

### Available Resources

The SDK provides 32 resource classes covering all Etsy API v3 endpoints:

**Shop Management**: Shop, ShopSection, ProductionPartner, ProcessingProfile  
**Listings**: Listing, ListingImage, ListingVideo, ListingFile, ListingInventory, ListingProduct, ListingProperty, ListingTranslation, ListingVariationImage, ListingOffering  
**Orders**: Receipt, Transaction, Payment, Shipment  
**Shipping**: ShippingProfile, ShippingDestination, ShippingUpgrade, ShippingCarrier  
**Policies**: ReturnPolicy, HolidayPreference  
**Reviews**: Review  
**Users**: User, UserAddress  
**Taxonomies**: SellerTaxonomy, SellerTaxonomyProperty, BuyerTaxonomy, BuyerTaxonomyProperty  
**Financial**: LedgerEntry

## Advanced Features

### Direct API Requests

Access any endpoint directly:

```php
use Etsy\Etsy;

$etsy = new Etsy($clientId, $sharedSecret, $accessToken);

// Make custom request
$response = Etsy::$client->get("/application/shops/{$shopId}/stats", [
    'start_date' => '2024-01-01',
    'end_date' => '2024-12-31'
]);

// Convert to resource
$shop = Etsy::getResource($response, 'Shop');
```

### Configuration Options

```php
// Enable 404 exceptions
$etsy = new Etsy($clientId, $sharedSecret, $accessToken, [
    '404_error' => true  // Throw exception instead of returning null
]);
```

**📖 [Advanced Usage Guide](docs/ADVANCED_USAGE.md)** - Direct requests, configurations, performance optimization

## Version Information

- **Current Version**: 1.2.0
- **PHP Requirement**: ^8.0
- **API Version**: Etsy API v3
- **License**: MIT

### Recent Changes (v1.2.0)

- **Breaking**: Shared secret now required in Client and Etsy constructors
- Fixed: Listing variation image return type
- See [CHANGELOG.md](CHANGELOG.md) for full history
- See [Migration Guide](docs/MIGRATION_GUIDE.md) for upgrade instructions

## Testing

Test your connection:

```php
use Etsy\OAuth\Client;

$client = new Client($clientId, $sharedSecret);
$appId = $client->ping();

if ($appId) {
    echo "Connected! Application ID: {$appId}\n";
}
```

## Support & Resources

### Getting Help

- **📖 [Documentation](docs/)** - Comprehensive guides and references
- **🐛 [Issues](https://github.com/rhysnhall/etsy-php-sdk/issues)** - Report bugs or request features
- **💬 [Email](mailto:hello@rhyshall.com)** - Contact the maintainer
- **📚 [Etsy API Docs](https://developers.etsy.com/documentation)** - Official API documentation

### Quick Support Checklist

Before opening an issue:

1. Check [Troubleshooting Guide](docs/TROUBLESHOOTING.md)
2. Review [Examples](docs/EXAMPLES.md) for similar use cases
3. Verify you're using the latest version
4. Test with the `ping()` method
5. Check Etsy API status

When reporting issues, include:
- SDK version (composer show rhysnhall/etsy-php-sdk)
- PHP version (php -v)
- Error messages and stack traces
- Code sample demonstrating the issue
- Expected vs actual behavior

## Contributing

Contributions are welcome! Here's how to help:

1. **Report Bugs**: Open an issue with details and reproduction steps
2. **Suggest Features**: Describe your use case and proposed solution
3. **Submit PRs**: Fork, create a branch, make changes, and submit
4. **Improve Docs**: Help make documentation clearer

Before opening a pull request:
- Discuss the proposed changes via GitHub issue or email
- Follow existing code style and conventions
- Test your changes thoroughly
- Update documentation if needed

## License

This project is licensed under the MIT License - see [LICENSE.md](LICENSE.md) for details.

## Credits

Created and maintained by [Rhys Hall](https://github.com/rhysnhall)

Special thanks to all [contributors](https://github.com/rhysnhall/etsy-php-sdk/graphs/contributors).

---

**Note**: This SDK is not officially affiliated with Etsy, Inc. It is an independent open-source project that provides a convenient PHP interface to the Etsy API v3.

For official Etsy API documentation, visit [`developers.etsy.com`](https://developers.etsy.com).
