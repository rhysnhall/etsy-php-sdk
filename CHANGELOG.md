# Changelog

## v0.3.0

### New
* Added `config` property to the Client class. This currently only support the value '404_error' which when set to true will throw an error when a resource returns 404 instead of returning a null value. This value is unset/false by default.

### Fixed issues
* Fixed breaking issue with class names in the resource updateRequest method. This issue only relates to Linux environments. [#6] (https://github.com/rhysnhall/etsy-php-sdk/issues/6)

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
