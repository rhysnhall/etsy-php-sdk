<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * @link https://developers.etsy.com/documentation/reference#tag/Shop-ProcessingProfiles
 * @author Rhys Hall hello@rhyshall.com
 */
class ProcessingProfile extends Resource {

  /**
   * @var array
   */
  protected $_saveable = [
    'readiness_state',
    'min_processing_time',
    'max_processing_time',
    'processing_time_unit'
  ];

  /**
   * Get all processing profiles for a shop.
   * 
   * @param int $shop_id
   * @return Etsy\Collection[Etsy\Resources\ProcessingProfile]
   */
  public static function all(
    int $shop_id,
    array $params = []
  ): \Etsy\Collection {
    return self::request(
      "GET",
      "/application/shops/{$shop_id}/readiness-state-definitions",
      "ProcessingProfile",
      $params
    );
  }

  /**
   * Get a processing profile.
   * 
   * @param int $shop_id
   * @param int $readiness_state_id
   * @return Etsy\Resources\ProcessingProfile
   */
  public static function get(
    int $shop_id,
    int $readiness_state_id
  ): ?\Etsy\Resources\ProcessingProfile {
    return self::request(
      "GET",
      "/application/shops/{$shop_id}/readiness-state-definitions/{$readiness_state_id}",
      "ProcessingProfile"
    );
  }

  /**
   * Create a new shop processing profile.
   * 
   * @param int $shop_id
   * @param array $data
   * @return Etsy\Resources\ProcessingProfile
   */
  public static function create(
    int $shop_id,
    array $data
  ): ?\Etsy\Resources\ProcessingProfile {
    return self::request(
      "POST",
      "/application/shops/{$shop_id}/readiness-state-definitions",
      "ProcessingProfile",
      $data
    );
  }

  /**
   * Update a shop processing profile.
   * 
   * @param int $shop_id
   * @param int $readiness_state_id
   * @param array $data
   * @return Etsy\Resources\ProcessingProfile
   */
  public static function update(
    int $shop_id,
    int $readiness_state_id,
    array $data
  ): ?\Etsy\Resources\ProcessingProfile {
    return self::request(
      "PUT",
      "/application/shops/{$shop_id}/readiness-state-definitions/{$readiness_state_id}",
      "ProcessingProfile",
      $data
    );
  }

  /**
   * Delete a shop processing profile.
   * 
   * @param int $shop_id
   * @param int $readiness_state_id
   * @return bool
   */
  public static function delete(
    int $shop_id,
    int $readiness_state_id
  ): bool {
    return self::deleteRequest(
      "/application/shops/{$shop_id}/readiness-state-definitions/{$readiness_state_id}"
    );
  }

  /**
   * Saves updates to the current processing profile.
   * 
   * @param array $data
   * @return Etsy\Resources\ProcessingProfile
   */
  public function save(
    ?array $data = null
  ): \Etsy\Resources\ProcessingProfile {
    if(!$data) {
      $data = $this->getSaveData();
    }
    if(count($data) == 0) {
      return $this;
    }
    return $this->updateRequest(
      "/application/shops/{$this->shop_id}/readiness-state-definitions/{$this->readiness_state_id}",
      $data
    );
  }
}