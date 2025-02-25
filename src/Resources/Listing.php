<?php

namespace Etsy\Resources;

use Etsy\Resource;
use Etsy\Exception\ApiException;
use Etsy\Resources\{
  Review,
  Transaction,
  ListingProperty,
  ListingFile,
  ListingImage,
  ListingVideo,
  ListingVariationImage,
  ListingInventory,
  ListingProduct,
  ListingTranslation
};

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
  protected $_saveable = [
    'image_ids',
    'title',
    'description',
    'materials',
    'should_auto_renew',
    'shipping_profile_id',
    'return_policy_id',
    'shop_section_id',
    'item_weight',
    'item_length',
    'item_width',
    'item_height',
    'item_weight_unit',
    'item_dimensions_unit',
    'is_taxable',
    'taxonomy_id',
    'tags',
    'who_made',
    'when_made',
    'featured_rank',
    'is_personalizable',
    'personalization_is_required',
    'personalization_char_count_max',
    'personalization_instructions',
    'state',
    'is_supply',
    'production_partner_ids',
    'type'
  ];

  /**
   * @var array
   */
  protected $_associations = [
    "Shop" => "Shop",
    "User" => "User",
    "Images" => "ListingImage"
  ];

  /**
   * Get all active listings on Etsy.
   * 
   * @param array $params
   * @return \Etsy\Collection[Etsy\Resources\Listing]
   */
  public static function all(
    array $params = []
  ): \Etsy\Collection {
    return self::request(
      "GET",
      "/application/listings/active",
      "Listing",
      $params
    );
  }

  /**
   * Get all active listings on Etsy. Filtered by listing ID. Support upto 100 IDs.
   * 
   * @param array $params
   * @return \Etsy\Collection[Etsy\Resources\Listing]
   */
  public static function allByIds(
    string|array $listing_ids,
    array $includes = []
  ): \Etsy\Collection {
    $params = [
      'listing_ids' => $listing_ids
    ];
    if(count($includes) > 0) {
      $params['includes'] = $includes;
    }
    return self::request(
      "GET",
      "/application/listings/batch",
      "Listing",
      $params
    );
  }

  /**
   * Get all listings for a shop.
   * 
   * @param int $shop_id
   * @param array $params
   * @return \Etsy\Collection[Etsy\Resources\Listing]
   */
  public static function allByShop(
    int $shop_id,
    array $params = []
  ): \Etsy\Collection {
    return self::request(
      "GET",
      "/application/shops/{$shop_id}/listings",
      "Listing",
      $params
    );
  }

  /**
   * Get all active listings for a shop.
   * 
   * @param int $shop_id
   * @param array $params
   * @return \Etsy\Collection[Etsy\Resources\Listing]
   */
  public static function allActiveByShop(
    int $shop_id,
    array $params = []
  ): \Etsy\Collection {
    return self::request(
      "GET",
      "/application/shops/{$shop_id}/listings/active",
      "Listing",
      $params
    );
  }

  /**
   * Get all featured listings for a shop.
   * 
   * @param int $shop_id
   * @param array $params
   * @return \Etsy\Collection[Etsy\Resources\Listing]
   */
  public static function allFeaturedByShop(
    int $shop_id,
    array $params = []
  ): \Etsy\Collection {
    return self::request(
      "GET",
      "/application/shops/{$shop_id}/listings/featured",
      "Listing",
      $params
    );
  }

  /**
   * Get all listings from a receipt.
   * 
   * @param int $shop_id
   * @param int $receipt_id
   * @param array $params
   * @return \Etsy\Collection[Etsy\Resources\Listing]
   */
  public static function allByReceipt(
    int $shop_id,
    int $receipt_id,
    array $params = []
  ): \Etsy\Collection {
    return self::request(
      "GET",
      "/application/shops/{$shop_id}/receipts/{$receipt_id}/listings",
      "Listing",
      $params
    );
  }

  /**
   * Get all the listings within a shop return policy.
   * 
   * @param int $shop_id
   * @param int $policy_id
   * @return \Etsy\Collection[\Etsy\Resources\Listing]
   */
  public static function allByReturnPolicy(
    int $shop_id,
    int $policy_id
  ): \Etsy\Collection {
    return self::request(
      "GET",
      "/application/shops/{$shop_id}/policies/return/{$policy_id}/listings",
      "Listing"
    );
  }

  /**
   * Get all listings withing specified shop sections.
   * 
   * @param int $shop_id
   * @param array|int $section_ids
   * @param array $params
   * @return \Etsy\Collection[\Etsy\Resources\Listing]
   */
  public static function allByShopSections(
    int $shop_id,
    array|int $section_ids,
    array $params = []
  ): \Etsy\Collection {
    $params['shop_section_ids'] = $section_ids;
    return self::request(
      "GET",
      "/application/shops/{$shop_id}/shop-sections/listings",
      "Listing",
      $params
    );
  }

  /**
   * Get a listing.
   * 
   * @param int $listing_id
   * @param array $params
   * @return \Etsy\Resources\Listing
   */
  public static function get(
    int $listing_id,
    array $params = []
  ): ?\Etsy\Resources\Listing {
    return self::request(
      "GET",
      "/application/listings/{$listing_id}",
      "Listing",
      $params
    );
  }

  /**
   * Create a draft Etsy listing.
   * 
   * @param int $shop_id
   * @param array $data
   * @return \Etsy\Resources\Listing
   */
  public static function create(
    int $shop_id,
    array $data
  ): ?\Etsy\Resources\Listing {
    return self::request(
      "POST",
      "/application/shops/{$shop_id}/listings",
      "Listing",
      $data
    );
  }

  /**
   * Delete an Etsy listing.
   * 
   * @param int $listing_id
   * @return bool
   */
  public static function delete(
    int $listing_id
  ): bool {
    return self::deleteRequest(
      "/application/listings/{$listing_id}"
    );
  }

  /**
   * Updates an Etsy listing.
   * 
   * @param int $shop_id
   * @param int $listing_id
   * @param array $data
   * @return \Etsy\Resources\Listing
   */
  public static function update(
    int $shop_id,
    int $listing_id,
    array $data
  ): ?\Etsy\Resources\Listing {
    return self::request(
      "PATCH",
      "/application/shops/{$shop_id}/listings/{$listing_id}",
      "Listing",
      $data
    );
  }

  /**
   * Saves updates to the current listing.
   * 
   * @param array $data
   * @return \Etsy\Resources\Listing
   */
  public function save(
    ?array $data = null
  ): \Etsy\Resources\Listing {
    if(!$data) {
      $data = $this->getSaveData(true);
    }
    if(count($data) == 0) {
      return $this;
    }
    return $this->updateRequest(
      "/application/shops/{$this->shop_id}/listings/{$this->listing_id}",
      $data,
      "PATCH"
    );
  }

  /**
   * Get all reviews for the listing.
   * 
   * @param array $params
   * @return \Etsy\Collection[Etsy\Resources\Review]
   */
  public function reviews(
    array $params = []
  ): \Etsy\Collection {
    return Review::allByListing($this->listing_id, $params);
  }

  /**
   * Get all transactions for the listing.
   * 
   * @param array @params
   * @return \Etsy\Collection[Etsy\Resources\Transaction]
   */
  public function transactions(
    array $params = []
  ): \Etsy\Collection {
    return Transaction::allByListing($this->shop_id, $this->listing_id, $params);
  }

  /**
   * Get all properties for the listing.
   * 
   * @return \Etsy\Collection[Etsy\Resources\ListingProperty]
   */
  public function properties(): \Etsy\Collection {
    return ListingProperty::all(
      $this->shop_id,
      $this->listing_id
    );
  }

  /**
   * Get all files for the listing.
   * 
   * @return \Etsy\Collection[Etsy\Resources\ListingFile]
   */
  public function files(): \Etsy\Collection {
    return ListingFile::all(
      $this->shop_id,
      $this->listing_id
    );
  }

  /**
   * Get a specific listing file.
   * 
   * @param int $file_id
   * @return \Etsy\Resources\ListingFile
   */
  public function file(
    int $file_id
  ): ?\Etsy\Resources\ListingFile {
    return ListingFile::get(
      $this->shop_id,
      $this->listing_id,
      $file_id
    );
  }

  /**
   * Link a file to the listing.
   * 
   * @param int $file_id
   * @param int $rank
   * @return \Etsy\Resources\ListingFile
   */
  public function linkFile(
    int $file_id,
    int $rank = 1
  ): ?\Etsy\Resources\ListingFile {
    return ListingFile::create(
      $this->shop_id,
      $this->listing_id,
      [
        'listing_file_id' => $file_id,
        'rank' => 1
      ]
    );
  }

  /**
   * Get the images for the listing.
   * 
   * @return \Etsy\Collection[\Etsy\Resources\ListingImage]
   */
  public function images(): \Etsy\Collection {
    return ListingImage::all(
      $this->listing_id
    );
  }

  /**
   * Get a specific listing image.
   * 
   * @param int $image_id
   * @return \Etsy\Resources\ListingImage
   */
  public function image(
    int $image_id
  ): ?\Etsy\Resources\ListingImage {
    return ListingImage::get(
      $this->listing_id,
      $image_id
    );
  }

  /**
   * Link an existing image to the listing.
   * 
   * @param int $image_id
   * @param array $options
   * @return \Etsy\Resources\ListingImage
   */
  public function linkImage(
    int $image_id,
    $options = []
  ): ?\Etsy\Resources\ListingImage {
    $options['listing_image_id'] = $image_id;
    return ListingImage::create(
      $this->shop_id,
      $this->listing_id,
      $options
    );
  }

  /**
   * Get the variation images for the listing.
   * 
   * @return \Etsy\Collection[\Etsy\Resources\ListingVariationImage]
   */
  public function variationImages(): \Etsy\Collection {
    return ListingVariationImage::all(
      $this->shop_id,
      $this->listing_id
    );
  }

  /**
   * Get the videos for the listing.
   * 
   * @return \Etsy\Collection[\Etsy\Resources\ListingVideo]
   */
  public function videos(): \Etsy\Collection {
    return ListingVideo::all(
      $this->listing_id
    );
  }

  /**
   * Get a specific listing image.
   * 
   * @param int $video_id
   * @return \Etsy\Resources\ListingVideo
   */
  public function video(
    int $video_id
  ): ?\Etsy\Resources\ListingVideo {
    return ListingVideo::get(
      $this->listing_id,
      $video_id
    );
  }

  /**
   * Link an existing image to the listing.
   * 
   * @param int $video_id
   * @return \Etsy\Resources\ListingVideo
   */
  public function linkVideo(
    int $video_id
  ): ?\Etsy\Resources\ListingVideo {
    $data['video_id'] = $video_id;
    return ListingVideo::create(
      $this->shop_id,
      $this->listing_id,
      $data
    );
  }

  /**
   * Get the listing inventory.
   * 
   * @return \Etsy\Resources\ListingInventory
   */
  public function inventory(array $params = []): ?\Etsy\Resources\ListingInventory {
    return ListingInventory::get(
      $this->listing_id,
      $params
    );
  }

  /**
   * Get a listing product.
   * 
   * @param int $product_id
   * @return \Etsy\Resources\ListingProduct
   */
  public function product(
    int $product_id
  ): ?\Etsy\Resources\ListingProduct {
    return ListingProduct::get(
      $this->listing_id,
      $product_id
    );
  }

  /**
   * Get a listing translation.
   * 
   * @param string $language
   * @return \Etsy\Resources\ListingTranslation
   */
  public function translation(
    string $language
  ): ?\Etsy\Resources\ListingTranslation {
    return ListingTranslation::get(
      $this->shop_id,
      $this->listing_id,
      $language
    );
  }


}
