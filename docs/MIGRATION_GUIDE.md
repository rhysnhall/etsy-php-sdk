# Migration Guide

Guide for migrating between versions of the Etsy PHP SDK.

## Table of Contents

- [From v1.1 to v1.2](#from-v11-to-v12)
- [From v1.0 to v1.1](#from-v10-to-v11)
- [From v0.x to v1.0](#from-v0x-to-v10)
- [From Etsy API v2 to v3](#from-etsy-api-v2-to-v3)

## From v1.1 to v1.2

### Release Date
Version 1.2.0 - November 2023

### Breaking Changes

#### 1. Shared Secret Required

**Change**: The `x-api-key` header now requires the shared secret in format `keystring:secret`.

**v1.1.x**:
```php
$client = new Client($clientId);

$etsy = new Etsy($clientId, $accessToken);
```

**v1.2.0**:
```php
$client = new Client(
    $clientId,
    $sharedSecret  // Now required
);

$etsy = new Etsy(
    $clientId,
    $sharedSecret,  // Now required
    $accessToken
);
```

**Migration Steps**:

1. Get your shared secret from the [Etsy Developer Dashboard](https://www.etsy.com/developers/your-apps)
2. Update all `Client` instantiations to include shared secret
3. Update all `Etsy` instantiations to include shared secret

**Example Migration**:
```php
// Before (v1.1.x)
class MyEtsyService {
    private $clientId;
    private $accessToken;
    
    public function __construct($clientId, $accessToken) {
        $this->clientId = $clientId;
        $this->accessToken = $accessToken;
    }
    
    public function getClient() {
        return new Etsy($this->clientId, $this->accessToken);
    }
}

// After (v1.2.0)
class MyEtsyService {
    private $clientId;
    private $sharedSecret;
    private $accessToken;
    
    public function __construct($clientId, $sharedSecret, $accessToken) {
        $this->clientId = $clientId;
        $this->sharedSecret = $sharedSecret;  // Added
        $this->accessToken = $accessToken;
    }
    
    public function getClient() {
        return new Etsy(
            $this->clientId,
            $this->sharedSecret,  // Added
            $this->accessToken
        );
    }
}
```

### Bug Fixes

- Fixed return type for listing variation image (#41)

### Non-Breaking Changes

No other changes affect existing code.

## From v1.0 to v1.1

### Release Date
Version 1.1.0 - Mid 2023

### Breaking Changes

#### 1. User::getShop() Removed

**Change**: `User::getShop()` method removed in favor of `User::shop()`.

**v1.0.x**:
```php
$user = User::get();
$shop = $user->getShop();
```

**v1.1.0**:
```php
$user = User::get();
$shop = $user->shop();
```

**Migration**: Replace all `getShop()` calls with `shop()`.

#### 2. Shop::count() Removed

**Change**: Static `Shop::count()` method removed.

**v1.0.x**:
```php
$count = Shop::count($keyword);
```

**v1.1.0**:
```php
$shops = Shop::all($keyword);
$count = $shops->count;  // Use collection count property
```

**Migration**: Use `Shop::all()` and access the `count` property.

#### 3. Transaction::allbyReceipt() Renamed

**Change**: Fixed typo in method name.

**v1.0.x**:
```php
$transactions = Transaction::allbyReceipt($shopId, $receiptId);
```

**v1.1.0**:
```php
$transactions = Transaction::allByReceipt($shopId, $receiptId);
```

**Migration**: Fix capitalization (lowercase 'b' to uppercase 'B').

### New Features

#### 1. ProcessingProfile Resource

New resource for managing order processing profiles:

```php
use Etsy\Resources\ProcessingProfile;

$profiles = ProcessingProfile::all($shopId);
$profile = ProcessingProfile::get($shopId, $profileId);
```

#### 2. Shop::getByUserId()

New method to get shop by user ID:

```php
$shop = Shop::getByUserId($userId);
```

#### 3. Enhanced Listing Methods

New methods for file and media uploads:

```php
// Upload file
$listing->uploadFile($filePath, $fileName);

// Upload image
$listing->uploadImage($imagePath, ['rank' => 1]);

// Upload video
$listing->uploadVideo($videoPath, $fileName);
```

#### 4. Updated Saveable Properties

- Updated `ReturnPolicy` saveable properties
- Updated `ShippingUpgrade` saveable properties
- Added `$_saveable` to `ListingTranslation`

#### 5. New Associations

- `ShippingProfile` association in Listing
- `ListingVideo` association in Listing

### Bug Fixes

- Fixed `Request::prepareFile()` to support video uploads
- Fixed typos in Listing resource

### Migration Checklist

- [ ] Replace `User::getShop()` with `User::shop()`
- [ ] Replace `Shop::count()` with `Shop::all()->count`
- [ ] Fix `Transaction::allbyReceipt()` to `Transaction::allByReceipt()`
- [ ] Update to use new file upload methods (optional)
- [ ] Consider using new ProcessingProfile resource (optional)

## From v0.x to v1.0

### Release Date
Version 1.0.0 - 2022

### Breaking Changes

**Warning**: Version 1.0 is a complete rewrite. This is a major breaking update.

#### 1. Complete API Restructure

The SDK was completely rewritten to support Etsy API v3. Almost all code will need to be updated.

#### 2. Namespace Changes

**v0.x**:
```php
use Etsy\EtsyClient;
```

**v1.0.0**:
```php
use Etsy\Etsy;
use Etsy\Resources\Listing;
```

#### 3. Initialization Changes

**v0.x**:
```php
$etsy = new EtsyClient($apiKey);
```

**v1.0.0**:
```php
$etsy = new Etsy($clientId, $accessToken);
```

#### 4. Resource Access Changes

**v0.x**:
```php
$listing = $etsy->getListing($listingId);
$listings = $etsy->getShopListings($shopId);
```

**v1.0.0**:
```php
$listing = Listing::get($listingId);
$listings = Listing::allByShop($shopId);
```

#### 5. OAuth Changes

OAuth moved from v1.0 to v2.0 with PKCE.

**v0.x** (OAuth 1.0):
```php
$etsy->getOAuthRequestToken($callbackUrl);
```

**v1.0.0** (OAuth 2.0):
```php
$client = new Client($clientId);
[$verifier, $challenge] = $client->generateChallengeCode();
$url = $client->getAuthorizationUrl($redirectUri, $scopes, $challenge, $nonce);
```

### Migration Strategy

Given the extensive changes, we recommend:

1. **Parallel Implementation**: Implement v1.0 alongside v0.x
2. **Gradual Migration**: Migrate features one at a time
3. **Testing**: Thoroughly test each migrated feature
4. **Complete Switch**: Switch over when all features migrated

### Migration Steps

#### Step 1: Install New Version

```bash
composer require rhysnhall/etsy-php-sdk:^1.0
```

#### Step 2: Update Initialization

Create a new initialization file:

```php
// config/etsy_v1.php
use Etsy\Etsy;

$etsy = new Etsy($clientId, $accessToken);
```

#### Step 3: Update OAuth Flow

Implement new OAuth 2.0 flow:

```php
// oauth/authorize.php
use Etsy\OAuth\Client;

$client = new Client($clientId);
[$verifier, $challenge] = $client->generateChallengeCode();
$_SESSION['pkce_verifier'] = $verifier;

$nonce = $client->createNonce();
$_SESSION['oauth_nonce'] = $nonce;

$url = $client->getAuthorizationUrl(
    $redirectUri,
    $scopes,
    $challenge,
    $nonce
);

header("Location: {$url}");
```

```php
// oauth/callback.php
$client = new Client($clientId);
[$accessToken, $refreshToken] = $client->requestAccessToken(
    $redirectUri,
    $_GET['code'],
    $_SESSION['pkce_verifier']
);
```

#### Step 4: Migrate Resource Calls

Update each resource call:

**Listings**:
```php
// v0.x
$listing = $etsy->getListing($listingId);
$listings = $etsy->getShopListings($shopId);

// v1.0
$listing = Listing::get($listingId);
$listings = Listing::allByShop($shopId);
```

**Shops**:
```php
// v0.x
$shop = $etsy->getShop($shopId);

// v1.0
$shop = Shop::get($shopId);
```

**Orders/Receipts**:
```php
// v0.x
$orders = $etsy->getShopReceipts($shopId);

// v1.0
$receipts = Receipt::all($shopId);
```

#### Step 5: Update Property Access

Property names may have changed:

```php
// v1.0
$listing->listing_id;  // Not $listing->id
$listing->shop_id;     // Shop context
```

#### Step 6: Update Error Handling

```php
// v1.0
try {
    $listing = Listing::get($listingId);
} catch (\Etsy\Exception\RequestException $e) {
    // Handle error
}
```

### Features Removed in v1.0

- All API v2 specific methods
- OAuth 1.0 support (use `exchangeLegacyToken()` to migrate)
- Old method naming conventions

### Features Added in v1.0

- Full API v3 support
- Resource-based architecture
- Collection support with pagination
- Change tracking for updates
- Association resolution
- Modern OAuth 2.0 with PKCE

## From Etsy API v2 to v3

If you're using the SDK to migrate from Etsy API v2 to v3:

### Use This SDK

This SDK supports **only Etsy API v3**. For v2, you need a different library.

### Migrate OAuth Tokens

If you have existing OAuth 1.0 tokens:

```php
use Etsy\OAuth\Client;

$client = new Client($clientId);

try {
    [$accessToken, $refreshToken] = $client->exchangeLegacyToken($oauth1Token);
    
    // Store new OAuth 2.0 tokens
    saveTokens($accessToken, $refreshToken);
    
} catch (\Etsy\Exception\OAuthException $e) {
    // Token invalid or expired
    // User needs to re-authorize
}
```

### Key API v2 to v3 Changes

1. **OAuth**: v1.0 → v2.0 with PKCE
2. **Endpoints**: Different URL structure
3. **Parameters**: Different naming conventions
4. **Responses**: Different formats
5. **Features**: Some v2 features not in v3

### Migration Resources

- [Etsy API v3 Documentation](https://developers.etsy.com/documentation/)
- [API v2 to v3 Migration Guide](https://developers.etsy.com/documentation/migration)

## Summary

### Version History

| Version | Release | Major Changes |
|---------|---------|---------------|
| v1.2.0 | Nov 2023 | Shared secret required |
| v1.1.0 | Mid 2023 | New features, minor breaking changes |
| v1.0.0 | 2022 | Complete rewrite for API v3 |
| v0.x | Pre-2022 | API v2 support |

### Migration Priority

1. **Critical**: v1.1 → v1.2 (Add shared secret)
2. **Important**: v0.x → v1.0 (Complete rewrite)
3. **Recommended**: v1.0 → v1.1 (New features)

### Getting Help

- [GitHub Issues](https://github.com/rhysnhall/etsy-php-sdk/issues)
- [Changelog](../CHANGELOG.md)
- Email: hello@rhyshall.com

### Testing After Migration

Always test thoroughly after migration:

```php
// Test connection
$client = new Client($clientId, $sharedSecret);
$appId = $client->ping();
echo "Connected: {$appId}\n";

// Test authentication
$etsy = new Etsy($clientId, $sharedSecret, $accessToken);
$user = User::me();
echo "User: {$user->user_id}\n";

// Test basic operations
$listings = Listing::all(['limit' => 5]);
echo "Listings: " . $listings->count() . "\n";
```

### Best Practices

1. **Read Changelog**: Check CHANGELOG.md for all changes
2. **Test in Staging**: Test migrations in non-production environment
3. **Backup Tokens**: Keep backup of OAuth tokens
4. **Version Control**: Use semantic versioning in composer.json
5. **Monitor**: Watch for deprecation warnings

## Need Help?

If you encounter issues during migration:

1. Check [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
2. Review [EXAMPLES.md](EXAMPLES.md)
3. Read [API_REFERENCE.md](API_REFERENCE.md)
4. Open a GitHub issue
5. Contact the maintainer

Include in your help request:
- Current version
- Target version
- Error messages
- Code samples
- Migration steps attempted
