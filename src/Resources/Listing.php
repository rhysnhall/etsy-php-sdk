<?php

namespace Etsy\Resources;

use Etsy\Resource;
use Etsy\Exception\ApiException;

/**
 * Listing resource class. Represents an Etsy listing.
 *
 * @link https://developers.etsy.com/documentation/reference#tag/ShopListing
 * @author Rhys Hall hello@rhyshall.com
 */
class Listing extends Resource {

  /**
   * @var array
   */
  protected $_associations = [
    "Shop" => "Shop",
    "User" => "User",
    "Images" => "Image",
    "Translations" => "Translation",
    "Inventory" => "Inventory",
    "Videos" => "Video",
    "Shipping" => "Shipping"
  ];

  /**
   * Update the Etsy listing.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/updateListing
   * @param array $data
   * @return Etsy\Resources\Listing
   */
  public function update(array $data) {
    return $this->updateRequest(
      "/application/shops/{$this->shop_id}/listings/{$this->listing_id}",
      $data,
      'PATCH'
    );
  }

  /**
   * Delete the listing.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/deleteListing
   * @return boolean
   */
  public function delete() {
    return $this->deleteRequest(
      "/application/listings/{$this->listing_id}"
    );
  }

  /**
   * Get the listing properties associated with the listing.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/getListingProperties
   * @return Etsy\Collection[Etsy\Resources\ListingProperty]
   */
  public function getListingProperties() {
    return $this->request(
      "GET",
      "/application/shops/{$this->shop_id}/listings/{$this->listing_id}/properties",
      "ListingProperty"
    )
      ->append([
        'shop_id' => $this->shop_id,
        'listing_id' => $this->listing_id
      ]);
  }

  /**
   * Get a specific listing property.
   *
   * @NOTE This method is not ready for use and will return a 501 repsonse.
   * @link https://developers.etsy.com/documentation/reference#operation/getListingProperty
   * @param integer|string $property_id
   * @return Etsy\Resources\ListingProperty
   */
  public function getListingProperty($property_id) {
    $listing_property = $this->request(
      "GET",
      "/application/listings/{$this->listing_id}/properties/{$property_id}",
      "ListingProperty"
    );
    if($listing_property) {
      $listing_property->shop_id = $this->shop_id;
      $listing_property->listing_id = $this->listing_id;
    }
    return $listing_property;
  }

  /**
   * Get the listing files associated with the listing.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/getAllListingFiles
   * @return Etsy\Collection[Etsy\Resources\ListingFile]
   */
  public function getFiles() {
    return $this->request(
      "GET",
      "/application/shops/{$this->shop_id}/listings/{$this->listing_id}/files",
      "ListingFile"
    )
      ->append(["shop_id" => $this->shop_id]);
  }

  /**
   * Get a specific listing file.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/getListingFile
   * @param integer|string $listing_file_id
   * @return Etsy\Resources\ListingFile
   */
  public function getFile($listing_file_id) {
    $listing_file = $this->request(
      "GET",
      "/application/shops/{$this->shop_id}/listings/{$this->listing_id}/files/{$listing_file_id}",
      "ListingFile"
    );
    if($listing_file) {
      $listing_file->shop_id = $this->shop_id;
    }
    return $listing_file;
  }

  /**
   * Uploads a listing file.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/uploadListingFile
   * @param array $data
   * @return Etsy\Resources\ListingFile
   */
  public function uploadFile(array $data) {
    if(!isset($data['image']) && !isset($data['listing_image_id'])) {
      throw new ApiException("Request requires either 'listing_file_id' or 'file' paramater.");
    }
    $listing_file = $this->request(
      "POST",
      "/application/shops/{$this->shop_id}/listings/{$this->listing_id}/files",
      "ListingFile",
      $data
    );
    if($listing_file) {
      $listing_file->shop_id = $this->shop_id;
    }
    return $listing_file;
  }

  /**
   * Get the Listing Images for the listing.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/getListingImages
   * @return Etsy\Collection[Etsy\Resources\ListingImage]
   */
  public function getImages() {
    return $this->request(
      "GET",
      "/application/listings/{$this->listing_id}/images",
      "ListingImage"
    )
      ->append(["shop_id" => $this->shop_id]);
  }

  /**
   * Get a specific listing image.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/getListingImage
   * @param integer|string $listing_image_id
   * @return Etsy\Resources\ListingImage
   */
  public function getImage($listing_image_id) {
    $listing_image = $this->request(
      "GET",
      "/application/listings/{$this->listing_id}/images/{$listing_image_id}",
      "ListingImage"
    );
    if($listing_image) {
      $listing_image->shop_id = $this->shop_id;
    }
    return $listing_image;
  }
  
  /**
   * Upload a listing image.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/uploadListingImage
   * @param array $data
   * @return Etsy\Resources\ListingImage
   */
  public function uploadImage(array $data) {
    if(!isset($data['image']) && !isset($data['listing_image_id'])) {
      throw new ApiException("Request requires either 'listing_image_id' or 'image' paramater.");
    }
    $listing_image = $this->request(
      "POST",
      "/application/shops/{$this->shop_id}/listings/{$this->listing_id}/images",
      "ListingImage",
      $data
    );
    if($listing_image) {
      $listing_image->shop_id = $this->shop_id;
    }
    return $listing_image;
  }

  /**
   * Get the Listing Videos for the listing.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/getListingVideos
   * @return Etsy\Collection[Etsy\Resources\ListingVideo]
   */
  public function getVideos() {
    return $this->request(
      "GET",
      "/application/listings/{$this->listing_id}/videos",
      "ListingVideo"
    )
      ->append(["shop_id" => $this->shop_id]);
  }

  /**
   * Get a specific listing video.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/getListingVideo
   * @param integer|string $listing_video_id
   * @return Etsy\Resources\ListingVideo
   */
  public function getVideo($listing_video_id) {
    $listing_video = $this->request(
      "GET",
      "/application/listings/{$this->listing_id}/videos/{$listing_video_id}",
      "ListingVideo"
    );
    if($listing_video) {
      $listing_video->shop_id = $this->shop_id;
    }
    return $listing_video;
  }
  
  /**
   * Upload a listing video.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/uploadListingVideo
   * @param array $data
   * @return Etsy\Resources\ListingVideo
   */
  public function uploadVideo(array $data) {
    if(!isset($data['video']) && !isset($data['video_id'])) {
      throw new ApiException("Request requires either 'video_id' or 'video' paramater.");
    }
    $listing_video = $this->request(
      "POST",
      "/application/shops/{$this->shop_id}/listings/{$this->listing_id}/videos",
      "ListingVideo",
      $data
    );
    if($listing_video) {
      $listing_video->shop_id = $this->shop_id;
    }
    return $listing_video;
  }

  /**
   * Get the inventory for the listing.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/getListingInventory
   * @return Etsy\Resources\ListingInventory
   */
  public function getInventory() {
    $inventory = $this->request(
      "GET",
      "/application/listings/{$this->listing_id}/inventory",
      "ListingInventory"
    );
    // Assign the listing ID to associated inventory products.
    array_map(
      (function($product){
        $product->listing_id = $this->listing_id;
      }),
      ($inventory->products ?? [])
    );
    return $inventory;
  }

  /**
   * Update the inventory for the listing.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/updateListingInventory
   * @param array $data
   * @return Etsy\Resources\ListingInventory
   */
  public function updateInventory(array $data) {
    $inventory = $this->request(
      "PUT",
      "/application/listings/{$this->listing_id}/inventory",
      "ListingInventory",
      $data
    );
    // Assign the listing ID to associated inventory products.
    array_map(
      (function($product){
        $product->listing_id = $this->listing_id;
      }),
      ($inventory->products ?? [])
    );
    return $inventory;
  }

  /**
   * Get an offering for a listing. Use this method to bypass going through the ListingInventory resource.
   *
   * @link https://developers.etsy.com/documentation/reference#tag/ShopListing-Offering
   * @param integer|string $product_id
   * @param integer|string $product_offering_id
   * @return Etsy\Resources\ListingOffering
   */
  public function getOffering($product_id, $product_offering_id) {
    $offering = $this->request(
      "GET",
      "/application/listings/{$this->listing_id}/inventory/products/{$product_id}/offerings/{$product_offering_id}",
      "ListingOffering"
    );
    if($offering) {
      $offering->listing_id = $this->listing_id;
      $offering->product_id = $product_id;
    }
    return $offering;
  }

  /**
   * Get a specific product for a listing. Use this method to bypass going through the ListingInventory resource.
   *
   * @link https://developers.etsy.com/documentation/reference#tag/ShopListing-Product
   * @param integer|string $product_id
   * @return Etsy\Resources\ListingProduct
   */
  public function getProduct($product_id) {
    $product = $this->request(
      "GET",
      "/application/listings/{$this->listing_id}/inventory/products/{$product_id}",
      "ListingProduct"
    );
    if($product) {
      $product->listing_id = $this->listing_id;
    }
    return $product;
  }

  /**
   * Get a translation for the listing in a specific language.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/getListingTranslation
   * @param string $language
   * @return Etsy\Resources\ListingTranslation
   */
  public function getTranslation(
    string $language
  ) {
    $translation = $this->request(
      "GET",
      "/application/shops/{$this->shop_id}/listings/{$this->listing_id}/translations/{$language}",
      "ListingTranslation"
    );
    if($translation) {
      $translation->shop_id = $this->shop_id;
    }
    return $translation;
  }

  /**
   * Creates a listing translation.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/createListingTranslation
   * @param string $language
   * @param array $data
   * @return Etsy\Resources\ListingTranslation
   */
  public function createTranslation(
    string $language,
    array $data
  ) {
    $translation = $this->request(
      "POST",
      "/application/shops/{$this->shop_id}/listings/{$this->listing_id}/translations/{$language}",
      "ListingTranslation",
      $data
    );
    if($translation) {
      $translation->shop_id = $this->shop_id;
    }
    return $translation;
  }

  /**
   * Gets variation images for the listing.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/getListingVariationImages
   * @return Etsy\Collection[Etsy\Resources\ListingVariationImage]
   */
  public function getVariationImages() {
    return $this->request(
      "GET",
      "/application/shops/{$this->shop_id}/listings/{$this->listing_id}/variation-images",
      "ListingVariationImage"
    );
  }

  /**
   * Updates variation images for the listing. You MUST pass data for ALL variation images, including the ones you are not updating, as this method will override all existing variation images.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/updateVariationImages
   * @param array $data
   * @return Etsy\Collection[Etsy\Resources\ListingVariationImage]
   */
  public function updateVariationImages(array $data) {
    return $this->request(
      "POST",
      "/application/shops/{$this->shop_id}/listings/{$this->listing_id}/variation-images",
      "ListingVariationImage",
      $data
    );
  }

  /**
   * Get all reviews for the listing.
   *
   * @param array $params
   * @return Etsy\Collection[Etsy\Resources\Review]
   */
  public function getReviews(array $params = []) {
    return $this->request(
      "GET",
      "/application/listings/{$this->listing_id}/reviews",
      "Review",
      $params
    );
  }


}
