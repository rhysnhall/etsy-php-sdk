# Changelog

## Unreleased

### Fixes
- Methods that return a collection now return an empty collection when Etsy returns a 404, instead of failing with a PHP error. Set the `404_error` option to throw a `RequestException` instead, as before.
- `Listing::allByReturnPolicy()` and `ReturnPolicy::listings()` no longer throw a TypeError on every call.
- `Listing::save()` and `EconomicOperator::save()` now send changed array fields (such as `tags`, `materials` and `image_ids`) correctly. Previously they could send the old values of the changed items only, which replaced the field on Etsy with the wrong data.
- `save()` no longer throws a TypeError when Etsy returns a 404; it throws a `RequestException` with Etsy's error. After saving, every field Etsy returns is applied, including fields that were `null` before.
- `Receipt::save()` now saves changes to `is_shipped` and `is_paid`. Previously it ignored them.
- `ProcessingProfile::save()` now saves changes to `min_processing_days` and `max_processing_days`. Previously it ignored them.
- `HolidayPreference::all()` no longer throws an error on Etsy's response.
- `LedgerEntry::all()` now sets `shop_id` on each entry, so `$entry->payments()` works.
- Personalization included with a listing (via `includes`) can now be saved.
- `toArray()` and `toJson()` now include nested resources, such as included images and inventory products, instead of empty objects.

- `$receipt->transactions` and `$receipt->transactions()` use the transactions included with the receipt, instead of making another request per receipt.
- `$listing->personalization()` uses personalization included with the listing, instead of making another request.
- `ListingPersonalization::save()` now sends `supports_multiple_personalization_questions`, like `update()`. Without it, saving dropdown, upload or multiple questions could fail.
- `ShippingProfile::destinations()` and `upgrades()` no longer replace the profile's included destinations and upgrades with a collection.
- `Transaction::allByReceipt()` now sends its `$params`, and `Listing::linkFile()` now sends `$rank`.
- `isset()` and `empty()` now work on resource properties. Previously `isset($listing->title)` was always `false` and `empty($listing->tags)` was always `true`.
- `save()` can now clear a field. On `Listing`, `EconomicOperator`, `Receipt` and `ProcessingProfile`, setting a field to `null` or `[]` and saving sends an empty value. Previously the field was left out, so the save did nothing.
- `User::get()` returns `null` when the token has no user, instead of requesting `/users/` with no ID. Its parameter is now explicitly nullable, avoiding a PHP 8.4 deprecation notice.

### Changes
- Added `Resource::requestCollection()`, which works like `request()` but always returns a collection.
- Added `Receipt::createShipment()` and `$receipt->addTracking()` for submitting tracking. `Shipment::create()` still works but is deprecated: Etsy responds with the updated receipt, which it wrapped in a `Shipment`.
- `paginate()` now works for `Receipt`, `Transaction`, `LedgerEntry`, `ShippingDestination`, `ProcessingProfile` and `UserAddress` collections.
- Requests now time out: 10 seconds to connect and 60 seconds for a response (uploads have no response limit). Change these with the `timeout` and `connect_timeout` config options; `0` means no limit.
- `ListingPersonalization::save()` takes an optional second argument, `$supportMultipleQuestions`.

## v1.4.1

This release fixes several bugs and adds full documentation. There are no breaking changes.

### Fixes
- `ListingTranslation::save()` no longer throws a fatal error when called without arguments.
- Included associations, such as `$listing->images` after requesting `includes`, are now returned directly instead of being fetched again.
- `Collection::paginate()` no longer throws a fatal error when a later page fails to load, and no longer causes PHP 8.2 deprecation notices.
- `OAuthException` for failed token requests now includes the HTTP status code and the original exception via `getPrevious()`.

### Changes
- Added `Shop::featuredListings()`. The misspelled `featuedListings()` still works but is deprecated.
- `Listing::uploadImage()` no longer requires the `$options` argument.

### Documentation
- Rewrote the README as full documentation and added a [resource reference](docs/resources.md) covering every method, endpoint and required scope.

## v1.4.0

This update brings the SDK in line with the current Etsy Open API v3 spec. It adds support for GPSR economic operators and the new batch listing endpoints, and fixes several requests that no longer matched the API.

### New features

#### GPSR economic operators
The new `EconomicOperator` resource manages a shop's manufacturer and EU responsible person records, which are used for GPSR compliance on listings. Refer to Etsy's [GPSR Economic Operators Tutorial](https://developers.etsy.com/documentation/tutorials/gpsr-economic-operators) for full details.

```php
use Etsy\Resources\EconomicOperator;

// Get all economic operators for a shop.
$operators = EconomicOperator::all($shopId);

// Or via the Shop resource.
$operators = $shop->economicOperators();
$operator = $shop->economicOperator($operatorId);

// Create an economic operator.
$operator = EconomicOperator::create($shopId, [
  'operator_name' => 'Acme Ltd',
  'email' => 'compliance@example.com',
  'first_line' => '1 Example Street',
  'city' => 'Berlin',
  'zip' => '10115',
  'country_iso' => 'DE',
  'phone' => '0301234567'
]);

// Update an economic operator. Only the fields you change are sent.
$operator->city = 'Munich';
$operator->save();
```

Use the `economic_operator_id` as the `gpsr_manufacturer_id` or `gpsr_responsible_person_id` on a listing. The `gpsr_*` and `ecgt_*` (EU commercial guarantee) listing fields are now sent by the Listing resource's `save` method.

```php
$listing->gpsr_manufacturer_id = $operator->economic_operator_id;
$listing->save();
```

#### Batch listing inventory and shipping
Get the inventory or shipping profile for up to 100 listings in a single request.

```php
$listings = \Etsy\Resources\Listing::allInventoryByIds([$listingId1, $listingId2]);
foreach($listings->data as $listing) {
  // Uses the included inventory. No extra request is made.
  $inventory = $listing->inventory();
}

$listings = \Etsy\Resources\Listing::allShippingByIds([$listingId1, $listingId2]);
$profile = $listings->data[0]->shipping_profile;
```

#### JSON request bodies
`Resource::request()` and `Resource::updateRequest()` accept a new optional `$options` array. Pass `['json' => true]` to send the request body as JSON instead of form data.

### Fixes
- `ListingInventory::update`, `ListingPersonalization::update` and `save`, `ListingVariationImage::update` and `Shipment::create` now send JSON bodies, as Etsy requires for these endpoints.
- `ListingPersonalization::save` now sends a `POST` request instead of `PUT`.
- `ListingPersonalization::update` now sends `supports_multiple_personalization_questions` as `true` or `false`. Previously, passing `false` sent an empty value.
- `ListingOffering::get` no longer calls a removed endpoint. The offering is now found on its parent listing product. The new `ListingProduct::findOffering()` method finds an offering on a product you have already fetched.
- Listing inventory products now have their `listing_id` set.
- Connection failures and timeouts now throw a `RequestException` (or `OAuthException` for token requests) instead of causing a fatal error. The original exception is available via `getPrevious()`.
- Error responses that are not JSON no longer cause an error; the HTTP reason phrase is used as the message.
- Query parameters are no longer dropped from `DELETE` requests.

### Changes
- `UserAddress::delete` is deprecated and now throws an `SdkException`, as Etsy has removed this endpoint.
- `billing_r` and `feedback_r` have been removed from `PermissionScopes::ALL_SCOPES`, as Etsy no longer lists them.

## v1.3.0

This updates makes some major changes to how personalizations are handled in the Listing resource. For full details on what has changed refer to Etsy's [Multi Personalization Migration Guide](https://developers.etsy.com/documentation/tutorials/personalization-migration/).

### Changes
The `personalization_is_required`, `personalization_char_count_max` and `personalization_instructions` properties have been removed from the Listing resource `save` method. You can still update these fields up until Etsy removes them view the manual `update` method.

```php
// This will no longer work
$listing->personalization_instructions = 'test';
$listing->personalization_char_count_max = 256;
$listing->personalization_is_required = true;

// This will continue to work until April 9th, 2026.
\Etsy\Resources\Listing::update(
  $shopId,
  $listingId,
  [
    'personalization_instructions' => 'test',
    'personalization_char_count_max' => 256,
    'personalization_is_required' => true
  ]
);
```

Moving forward you should be managing listing personalizations via the new ListingPersonalization [endpoints](https://developers.etsy.com/documentation/reference#tag/ShopListing-Personalization) and resource within the SDK.

There are three ways to get a listings personalization settings.
```php
// Via the Listing resource when initially getting the listing..
$listing = \Etsy\Resources\Listing::get($shopId, $listingId, ['includes' => 'personalization']);
$personalization = $listing->personalization;

// Via the Listing resource after you have already gotten the listing.
$personalization = $listing->personalization();

// With the ListingPersonalization resource.
$personalization = \Etsy\Resources\ListingPersonalization::get($shopId, $listingId);
```

You can update the listing personalization via the resource `update` or `save` methods depending on your use case.
```php
// Update method
\Etsy\Resources\ListingPersonalization::update(
  $shopId, 
  $listingId,
  [
    'personalization_questions' => $questions
  ]
);

// Save method
$personalization->personalization_questions = $questions;
$personalization->save();
```

Delete listing personalization data with the `delete` method.
```php
\Etsy\Resources\ListingPersonalization::delete($shopId, $listingId);
```

## v1.2.0
Added support for the new Etsy request header changes. The `x-api-key` header must now include your app's shared secret. The new header format is `x-api-key: keystring:secret`, instead of the previous `x-api-key: keystring`.

Pass the app's shared secret as the second parameter to both the `Client` and `Etsy` classes.

```php
$client = new Client(
  $clientId,
  $sharedSecret
);

$etsy = new Etsy(
  $clientId,
  $sharedSecret,
  $accessToken
);
```

### Fixed issues
* Fixed return type for listing variation image. closes [#41](https://github.com/rhysnhall/etsy-php-sdk/issues/41)

## v1.1.0
### Fixed issues
* Update saveable values on ReturnPolicy resource.
* Update saveable values on ShippingUpgrade resource.
* Correct name of Transaction::allbyReceipt to Transaction::allByReceipt
* Update Request::prepareFile to correctly support video uploads.
* Add $_saveable property to ListingTranslation resource.

### Changes
* Add getByUserId() method to Shop resource.
* Add new ProcessingProfile resource.
* Add optional params to Transaction::allByReceipt method to cater for new query paramaters associated with this api call.
* Add ShippingProfile and ListingVideo associations to the Listing resource.
* Add assignShopIdToIncludedResources() method to Listing resource to ensure associated resources are assigned the shop ID.
* Changed Request::prepareFile method to support manual upload of images and files (instead of the method forcing it).
* Add uploadFile() method to Listings resource.
* Add uploadImage() method to Listings resource.
* Add uploadVideo() method to Listings resource.

### Breaking changes
* Remove the User::getShop method.
* Remove the Shop::count static method. You can get the count for a shops by referencing the count property on the Shop collection after calling Shop::all().

## v1.0.2
### Fisued issues
* Fix typos in Listing resource.
* Pass additional params in the prepareFile Request util.

## v1.0.1
### Fixed issues
* Correct ListingInventory::get to be a static method
* Add support for params to Listing->inventory method

## v1.0
### Changes
* Everything. Unfortunately, this is a breaking update.

## v0.3.2
### Fixed issues
* Fixed issue with associated properties being incorrectly updated on create() methods resulting in "Indirect modification of overloaded property" error. [Issue #9](https://github.com/rhysnhall/etsy-php-sdk/issues/9)

## v0.3.1

### Fixed issues
* Add check and exception when a null or empty client ID is passed to the Oauth/Client class. [Issue #8](https://github.com/rhysnhall/etsy-php-sdk/issues/8)
* Updated handleAcessTokenError() method to check for the existence of 'error_description' in the response body. [Issue #8](https://github.com/rhysnhall/etsy-php-sdk/issues/8)

## v0.3.0

### New
* Added `config` property to the Client class. This currently only supports the value '404_error' which when set to true will throw an error when a resource returns 404 instead of returning a null value. This value is unset/false by default.

### Fixed issues
* Fixed breaking issue with class names in the resource updateRequest method. This issue only relates to Linux environments. [#6](https://github.com/rhysnhall/etsy-php-sdk/issues/6)

### Minor notes
* Fixed some typos in Client error messages.

## v0.2.1

### Fixed issues
* updateVariationImages() method in Listings resource now correctly uses POST method [#5](https://github.com/rhysnhall/etsy-php-sdk/issues/5)
* Typo in ShippingUpgrade update and delete methods [#7](https://github.com/rhysnhall/etsy-php-sdk/issues/7)
* When uploading an image or file the Etsy Client will now POST the data as multipart.

## v0.2
Finalise basics of all methods as per Etsy's API reference.

* Add ListingFile
* Add ListingImage
* Add ListingInventory
* Add ListingOffering
* Add ListingProduct
* Add ListingTranslation
* Add ListingVariationImage

## v0.1.2
* Add Shop Listing methods
* Update Request Utility getParamaters method


## v0.1.1
* Fix update & delete methods for ShippingProfiles
* Add create draft listing method
* Add get listing method

## v0.1
First actual version.

* Added support for the Etsy API v3.
* Removed support for Etsy API v2
