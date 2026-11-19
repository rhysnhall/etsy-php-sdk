# Etsy PHP SDK

[![Latest version](https://img.shields.io/packagist/v/rhysnhall/etsy-php-sdk.svg)](https://packagist.org/packages/rhysnhall/etsy-php-sdk)
[![License](https://img.shields.io/packagist/l/rhysnhall/etsy-php-sdk.svg)](LICENSE.md)

A PHP SDK for the [Etsy Open API v3](https://developers.etsy.com/documentation/). It handles OAuth 2.0 authentication and wraps the API's endpoints in resource classes, so you can work with listings, orders, shipping profiles and more as PHP objects.

```php
use Etsy\Etsy;
use Etsy\Resources\Listing;

new Etsy($keystring, $sharedSecret, $accessToken);

$listing = Listing::get($listingId);
$listing->title = 'Hand-thrown stoneware mug';
$listing->save();
```

## Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Authentication](#authentication)
- [Making requests](#making-requests)
- [Resources](#resources)
- [Collections and pagination](#collections-and-pagination)
- [Creating, updating and deleting](#creating-updating-and-deleting)
- [Uploading files](#uploading-files)
- [Direct requests](#direct-requests)
- [Error handling](#error-handling)
- [Resource reference](#resource-reference)
- [Upgrading](#upgrading)
- [Contributing](#contributing)

## Requirements

- PHP 8.0 or later
- An Etsy app. Register one at [Your Apps](https://www.etsy.com/developers/your-apps) to get its **keystring** and **shared secret**.

## Installation

```
composer require rhysnhall/etsy-php-sdk
```

## Authentication

Etsy uses OAuth 2.0 with [PKCE](https://developers.etsy.com/documentation/essentials/authentication#proof-key-for-code-exchange-pkce). A shop owner approves your app on Etsy, and you receive an access token that lets you act on their behalf. The SDK's `Etsy\OAuth\Client` handles each step.

Before you start, add your callback URL to your app on [Your Apps](https://www.etsy.com/developers/your-apps). Etsy only redirects to registered URLs.

### 1. Send the user to Etsy

Generate a PKCE code challenge and a state value, store both in the user's session, then redirect them to Etsy:

```php
use Etsy\OAuth\Client;

session_start();

$client = new Client($keystring, $sharedSecret);

[$verifier, $codeChallenge] = $client->generateChallengeCode();
$state = $client->createNonce();

$_SESSION['etsy_verifier'] = $verifier;
$_SESSION['etsy_state'] = $state;

$url = $client->getAuthorizationUrl(
  'https://example.com/etsy/callback', // your registered callback URL
  ['listings_r', 'listings_w', 'shops_r'], // scopes
  $codeChallenge,
  $state
);

header("Location: {$url}");
```

### 2. Handle the callback

Etsy redirects back to your callback URL with a `code` and the `state` you sent. Check the state matches, then exchange the code for tokens:

```php
session_start();

if(isset($_GET['error'])) {
  exit("Authorization failed: {$_GET['error']}");
}
if(!hash_equals($_SESSION['etsy_state'] ?? '', $_GET['state'] ?? '')) {
  exit('Invalid state.');
}

$client = new Client($keystring, $sharedSecret);

$tokens = $client->requestAccessToken(
  'https://example.com/etsy/callback', // must match step 1
  $_GET['code'],
  $_SESSION['etsy_verifier']
);

$accessToken = $tokens['access_token'];
$refreshToken = $tokens['refresh_token'];
$expiresAt = $tokens['expires_at']; // Unix time the access token expires
```

Store both tokens. The access token lasts **1 hour**; the refresh token lasts **90 days**. The array also includes `expires_in` (the access token's lifetime in seconds) and `expires_at`, so you can refresh before the token runs out.

### Refreshing the access token

Use the refresh token to get a new access token. This works even after the access token has expired. Store the refresh token returned with it for next time.

```php
$tokens = $client->refreshAccessToken($refreshToken);
```

### Scopes

Request only the [scopes](https://developers.etsy.com/documentation/essentials/authentication#scopes) your app needs. The [resource reference](docs/resources.md) lists the scope each method requires. `Etsy\Utils\PermissionScopes::ALL_SCOPES` contains every scope if you need them all.

To see which scopes a token has:

```php
$etsy = new Etsy($keystring, $sharedSecret, $accessToken);
$scopes = $etsy->scopes(); // e.g. ['listings_r', 'shops_r']
```

`Etsy::$client->scopes()` does the same, and you can pass it a different token to check.

### Legacy OAuth 1.0 tokens

If you have OAuth 1.0 tokens from Etsy's v2 API, exchange them for OAuth 2.0 tokens:

```php
$tokens = $client->exchangeLegacyToken($legacyToken);
```

## Making requests

Create an `Etsy` instance with your app's keystring and shared secret and the user's access token. This must happen before you use any resource class.

```php
use Etsy\Etsy;
use Etsy\Resources\{User, Shop};

$etsy = new Etsy($keystring, $sharedSecret, $accessToken);

$me = User::me(); // ->user_id and ->shop_id of the authenticated user
$shop = Shop::get($me->shop_id);
$listings = $shop->activeListings(['limit' => 25]);
```

The fourth argument is optional config:

| Option | Default | Description |
|---|---|---|
| `404_error` | `false` | Throw a `RequestException` when Etsy returns 404. See [Error handling](#error-handling). |
| `timeout` | `60` | Seconds to wait for a response before failing, or `0` for no limit. Uploads have no limit unless you set this. |
| `connect_timeout` | `10` | Seconds to wait to connect to Etsy, or `0` for no limit. |

Some endpoints are public and only need your app's keys. For those, you can leave out the access token:

```php
$etsy = new Etsy($keystring, $sharedSecret);
$listings = Listing::all(['keywords' => 'stoneware mug']);
```

## Resources

Requests return resource objects from the `Etsy\Resources` namespace, such as `Listing`, `Shop` and `Receipt`. Etsy's response fields are available as properties:

```php
$listing = Listing::get($listingId);

echo $listing->title;
echo $listing->state;      // e.g. "active"
echo $listing->price->amount / $listing->price->divisor;
```

Property names aren't case-sensitive, and missing properties return `null`.

### Associations

Related objects in a response are returned as resources too. Request them with Etsy's `includes` parameter where an endpoint supports it:

```php
$listing = Listing::get($listingId, ['includes' => 'Images']);

foreach($listing->images as $image) { // ListingImage resources
  echo $image->url_570xN;
}
```

Reading the property uses the included data. Calling the method of the same name, such as `$listing->images()`, always makes a new request.

### Instance methods

Resources have shortcut methods for related requests, so you don't need to pass IDs around:

```php
$shop = Shop::get($shopId);

$sections = $shop->sections();
$receipts = $shop->receipts(['limit' => 50]);
$profiles = $shop->shippingProfiles();

$listing = Listing::get($listingId);
$images = $listing->images();
$inventory = $listing->inventory();
$german = $listing->translation('de');
```

Methods like `inventory()`, `personalization()` and `translation()` use the data included with the resource when it's there, and only make a request when it isn't. For example, after `Listing::get($listingId, ['includes' => 'Translations'])`, `$listing->translation('de')` makes no request, and `$listing->translations` maps each language to a `ListingTranslation` (or `null` if there's no translation for it).

The [resource reference](docs/resources.md) lists every method.

### Converting to arrays and JSON

```php
$array = $listing->toArray();
$json = $listing->toJson();
```

## Collections and pagination

Methods that return more than one item return an `Etsy\Collection`. The resources are in its `data` property:

```php
$reviews = $shop->reviews();

foreach($reviews->data as $review) {
  echo $review->rating;
}

$first = $reviews->first(); // first resource, or false if empty
$count = $reviews->count(); // number of resources in this collection
$total = $reviews->count;   // Etsy's total number of matching results
```

### Pagination

Etsy returns 25 results per request by default and at most 100. Set `limit` and `offset` to page manually, or use `paginate()` to iterate across pages automatically:

```php
$listings = Listing::allActiveByShop($shopId, ['limit' => 100]);

foreach($listings->paginate(300) as $listing) {
  // Fetches further pages as needed.
}
```

`paginate($results)` keeps fetching pages until at least `$results` items have been returned (up to 500) or there are no more pages. It's available for `Listing`, `Shop`, `Review`, `Receipt`, `Transaction`, `LedgerEntry`, `ShippingDestination`, `ProcessingProfile` and `UserAddress` collections, and throws an `SdkException` for other resources.

### Other collection methods

```php
// Set a property on every resource in the collection.
$reviews->append(['shop_id' => $shopId]);

// An array of JSON strings, one per resource.
$json = $reviews->toJson();
```

## Creating, updating and deleting

Static methods take the IDs and data Etsy expects. The field names are Etsy's; see each endpoint in the [resource reference](docs/resources.md) for what it accepts.

```php
use Etsy\Resources\{Listing, ShopSection};

$section = ShopSection::create($shopId, ['title' => 'Mugs']);

$listing = Listing::create($shopId, [
  'quantity' => 1,
  'title' => 'Stoneware mug',
  'description' => 'Hand-thrown and glazed.',
  'price' => 32.00,
  'who_made' => 'i_did',
  'when_made' => 'made_to_order',
  'taxonomy_id' => $taxonomyId,
  'shipping_profile_id' => $shippingProfileId,
]); // created as a draft; see createDraftListing in the reference for all fields

Listing::update($shopId, $listing->listing_id, ['state' => 'active']);

Listing::delete($listingId); // returns true on success
```

### Saving changes to a resource

Many resources have a `save()` method. Change properties, then call `save()`:

```php
$listing = Listing::get($listingId);
$listing->title = 'Stoneware mug, speckled glaze';
$listing->description = 'Hand-thrown and finished in a speckled glaze.';
$listing->save();
```

`save()` sends the resource's editable fields, which are listed for each resource in the [resource reference](docs/resources.md):

- `Listing`, `EconomicOperator`, `Receipt` and `ProcessingProfile` send only the fields you changed, and skip the request if nothing changed. Array fields such as `tags` are sent whole.
- Other resources send all of their editable fields each time.

To clear a field on these resources, set it to `null` (or `[]` for an array field) and save. The SDK sends it as an empty value, which Etsy treats as a request to clear the field:

```php
$listing->tags = [];
$listing->item_weight = null;
$listing->save();
```

When you call `update()` or pass data to `save()` directly, a `null` value is left out of the request. Send `''` instead to clear the field.

You can also pass the data to send directly: `$listing->save(['title' => 'New title'])`.

After saving, the resource is updated with the values Etsy returns. If Etsy rejects the change, `save()` throws a `RequestException`, including for a 404 whatever the `404_error` setting.

### Shipping orders

Add tracking to a receipt to mark it as shipped. Etsy emails the buyer each time tracking is submitted, and returns the updated receipt:

```php
use Etsy\Resources\Receipt;

$receipt = Receipt::get($shopId, $receiptId);
$receipt->addTracking([
  'tracking_code' => '1Z999AA10123456784',
  'carrier_name' => 'ups',
]);

echo $receipt->is_shipped; // true

// Or without fetching the receipt first:
$receipt = Receipt::createShipment($shopId, $receiptId, $tracking);
```

A receipt's transactions (the items in the order) are included with it, so `$receipt->transactions` doesn't make another request.

### Inventory

Listing inventory (products, prices, quantities and SKUs) is updated as a whole and sent as JSON:

```php
use Etsy\Resources\ListingInventory;

ListingInventory::update($listingId, [
  'products' => [[
    'sku' => 'MUG-SPECKLED',
    'property_values' => [],
    'offerings' => [[
      'price' => 32.00,
      'quantity' => 5,
      'is_enabled' => true,
    ]],
  ]],
]);
```

Get the inventory for up to 100 of your listings in one request with `Listing::allInventoryByIds()`. See Etsy's [updateListingInventory reference](https://developers.etsy.com/documentation/reference#operation/updateListingInventory) for how products, offerings and variation properties fit together.

## Uploading files

Listing images, videos and digital files are uploaded as multipart form data. Pass a file path (or a URL, if PHP's `allow_url_fopen` is on), an open stream, or a stream or string as `contents`. If a path can't be read, the SDK throws a `RequestException` naming it, before anything is sent:

```php
use Etsy\Resources\{ListingImage, ListingVideo, ListingFile};

// From a path.
ListingImage::create($shopId, $listingId, [
  'image' => '/path/to/mug.jpg',
  'rank' => 1,
]);

// From a stream or string.
ListingImage::create($shopId, $listingId, [
  'image' => ['contents' => fopen('/path/to/mug.jpg', 'r')],
]);

// Videos and files also need a name.
ListingVideo::create($shopId, $listingId, [
  'video' => '/path/to/mug.mp4',
  'name' => 'mug.mp4',
]);

ListingFile::create($shopId, $listingId, [
  'file' => '/path/to/pattern.pdf',
  'name' => 'pattern.pdf',
]);
```

A `Listing` has shortcuts for the same uploads:

```php
$listing->uploadImage('/path/to/mug.jpg', ['rank' => 1]);
$listing->uploadVideo('/path/to/mug.mp4', 'mug.mp4');
$listing->uploadFile('/path/to/pattern.pdf', 'pattern.pdf');
```

## Direct requests

For anything the resource classes don't cover, use the HTTP client directly. Paths are relative to `https://api.etsy.com/v3`:

```php
$response = Etsy::$client->get('/application/listings/active', ['limit' => 25]);
// A plain object decoded from Etsy's JSON response.
```

`post()`, `put()`, `patch()` and `delete()` work the same way. Write requests are form encoded; pass `['json' => true]` as a third argument for endpoints that expect JSON:

```php
Etsy::$client->put("/application/listings/{$listingId}/inventory", $data, ['json' => true]);
```

To turn a response into a resource (or a collection, for responses with `results`), use `Etsy::getResource()`:

```php
$listings = Etsy::getResource(
  Etsy::$client->get('/application/listings/active'),
  'Listing'
);
```

## Error handling

The SDK throws these exceptions, all in the `Etsy\Exception` namespace:

| Exception | Thrown when |
|---|---|
| `RequestException` | Etsy returns an error response, or the request fails to connect or times out. The message includes Etsy's error. For HTTP errors, `getCode()` returns the status code. |
| `OAuthException` | A token request fails, or the client is created without a keystring or shared secret. |
| `SdkException` | A method is used in a way the SDK or Etsy doesn't support, such as paginating a collection that doesn't support it, or required input is missing, such as searching shops without a keyword. |

Searching shops without a keyword throws `ApiException`, which is a subclass of `SdkException`. `ApiException` is deprecated, so catch `SdkException` in new code.

The original Guzzle exception is available from `getPrevious()`.

```php
use Etsy\Exception\RequestException;

try {
  $listing = Listing::update($shopId, $listingId, ['state' => 'active']);
}
catch(RequestException $e) {
  error_log($e->getMessage()); // Received HTTP status code [400] with error "..."
}
```

### Not found responses

By default, a 404 from Etsy doesn't throw:

- Methods that return a single resource return `null`.
- Methods that return a collection return an empty collection.

```php
$listing = Listing::get($listingId);
if($listing === null) {
  // Not found.
}
```

To throw a `RequestException` on a 404 instead, like any other error, set the `404_error` option:

```php
$etsy = new Etsy($keystring, $sharedSecret, $accessToken, ['404_error' => true]);
```

## Resource reference

The [resource reference](docs/resources.md) documents every resource, method, endpoint and required scope. It's grouped as follows:

| Area | Resources |
|---|---|
| Users and shops | `User`, `UserAddress`, `Shop`, `ShopSection`, `HolidayPreference`, `ProductionPartner`, `EconomicOperator` |
| Listings | `Listing`, `ListingInventory`, `ListingProduct`, `ListingOffering`, `ListingProperty`, `ListingPersonalization`, `ListingTranslation` |
| Listing media and files | `ListingImage`, `ListingVariationImage`, `ListingVideo`, `ListingFile` |
| Orders and payments | `Receipt`, `Transaction`, `Shipment`, `Payment`, `LedgerEntry`, `Review` |
| Shipping, processing and returns | `ShippingProfile`, `ShippingDestination`, `ShippingUpgrade`, `ShippingCarrier`, `ProcessingProfile`, `ReturnPolicy` |
| Taxonomy | `SellerTaxonomy`, `SellerTaxonomyProperty`, `BuyerTaxonomy`, `BuyerTaxonomyProperty` |

For the fields each endpoint accepts and returns, see [Etsy's API reference](https://developers.etsy.com/documentation/reference).

## Upgrading

See the [changelog](CHANGELOG.md) for changes in each release, including anything you need to update.

## Contributing

Bug reports and pull requests are welcome. Before starting on a larger change, please open an issue or [email me](mailto:hello@rhyshall.com) to discuss it.

### Running the tests

The test suite uses a fake HTTP client, so it needs no Etsy credentials and makes no network requests:

```
composer install
composer test
```

To check the SDK against the live Etsy API with your own app and shop, see [dev/README.md](dev/README.md).

## License

MIT. See [LICENSE.md](LICENSE.md).
