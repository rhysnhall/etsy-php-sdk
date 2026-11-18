<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Listing Personalization class.
 *
 * @link https://developers.etsy.com/documentation/reference#tag/ShopListing-Personalization
 * @author Rhys Hall hello@rhyshall.com
 */
class ListingPersonalization extends Resource {

  /**
   * @var array
   */
  protected $_saveable = [
    'personalization_questions'
  ];

  /**
   * Get all personalizations for a listing.
   * 
   * @param int $listing_id
   * @return Etsy\Resources\ListingPersonalization
   */
  public static function get(
    int $shop_id,
    int $listing_id
  ): ?\Etsy\Resources\ListingPersonalization {
    $listingPersonalization = self::request(
      "GET",
      "/application/listings/{$listing_id}/personalization",
      "ListingPersonalization"
    );
    if($listingPersonalization) {
      $listingPersonalization->shop_id = $shop_id;
      $listingPersonalization->listing_id = $listing_id;
    }
    return $listingPersonalization;
  }

  /**
   * Updates a listing personalization.
   * 
   * @param int $shop_id
   * @param int $listing_id
   * @param array $data
   * @return Etsy\Resources\ListingPersonalization
   */
  public static function update(
    int $shop_id,
    int $listing_id,
    $data,
    $supportMultipleQuestions = true
  ): ?\Etsy\Resources\ListingPersonalization {
    $supportsMultiple = $supportMultipleQuestions ? 'true' : 'false';
    $listingPersonalization = self::request(
      "POST",
      "/application/shops/{$shop_id}/listings/{$listing_id}/personalization?supports_multiple_personalization_questions={$supportsMultiple}",
      "ListingPersonalization",
      $data,
      ['json' => true]
    );
    if($listingPersonalization) {
      $listingPersonalization->shop_id = $shop_id;
      $listingPersonalization->listing_id = $listing_id;
    }
    return $listingPersonalization;
  }

  /**
   * Saves updates to the current listing personalization.
   * 
   * @param array $data
   * @return \Etsy\Resources\ListingPersonalization
   */
  public function save(
    ?array $data = null,
    bool $supportMultipleQuestions = true
  ): \Etsy\Resources\ListingPersonalization {
    if(!$data) {
      $data = $this->getSaveData();
    }
    if(count($data) == 0) {
      return $this;
    }
    $supportsMultiple = $supportMultipleQuestions ? 'true' : 'false';
    return $this->updateRequest(
      "/application/shops/{$this->shop_id}/listings/{$this->listing_id}/personalization?supports_multiple_personalization_questions={$supportsMultiple}",
      $data,
      "POST",
      ['json' => true]
    );
  }

  /**
   * Delete a listing personalization.
   * 
   * @param int $shop_id
   * @param int $listing_id
   * @return bool
   */
  public static function delete(
    int $shop_id,
    int $listing_id
  ): bool {
    return self::deleteRequest(
      "/application/shops/{$shop_id}/listings/{$listing_id}/personalization"
    );
  }
}
