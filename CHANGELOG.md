# Changelog
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
