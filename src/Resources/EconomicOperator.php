<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * EconomicOperator resource class. Represents a shop's GPSR economic operator
 * (a manufacturer or EU responsible person record). Use the economic_operator_id
 * as gpsr_manufacturer_id or gpsr_responsible_person_id on a listing.
 *
 * @link https://developers.etsy.com/documentation/reference#tag/Shop-Economic-Operators
 * @link https://developers.etsy.com/documentation/tutorials/gpsr-economic-operators
 * @author Rhys Hall hello@rhyshall.com
 */
class EconomicOperator extends Resource {

  /**
   * @var array
   */
  protected $_saveable = [
    'operator_name',
    'email',
    'first_line',
    'second_line',
    'city',
    'state',
    'zip',
    'country_iso',
    'phone',
    'phone_country_iso',
    'trademark_name'
  ];

  /**
   * Get all economic operators for a shop. Deleted operators are not included.
   *
   * @param int $shop_id
   * @return \Etsy\Collection[\Etsy\Resources\EconomicOperator]
   */
  public static function all(
    int $shop_id
  ): \Etsy\Collection {
    return self::request(
      "GET",
      "/application/shops/{$shop_id}/economic-operators",
      "EconomicOperator"
    )->append(['shop_id' => $shop_id]);
  }

  /**
   * Get an economic operator.
   *
   * @param int $shop_id
   * @param int $economic_operator_id
   * @return \Etsy\Resources\EconomicOperator
   */
  public static function get(
    int $shop_id,
    int $economic_operator_id
  ): ?\Etsy\Resources\EconomicOperator {
    return self::request(
      "GET",
      "/application/shops/{$shop_id}/economic-operators/{$economic_operator_id}",
      "EconomicOperator"
    );
  }

  /**
   * Create an economic operator.
   *
   * Required: operator_name, email, first_line, city, zip, country_iso, phone.
   *
   * @param int $shop_id
   * @param array $data
   * @return \Etsy\Resources\EconomicOperator
   */
  public static function create(
    int $shop_id,
    array $data
  ): ?\Etsy\Resources\EconomicOperator {
    return self::request(
      "POST",
      "/application/shops/{$shop_id}/economic-operators",
      "EconomicOperator",
      $data
    );
  }

  /**
   * Update an economic operator. Only the fields provided are changed.
   *
   * @param int $shop_id
   * @param int $economic_operator_id
   * @param array $data
   * @return \Etsy\Resources\EconomicOperator
   */
  public static function update(
    int $shop_id,
    int $economic_operator_id,
    array $data
  ): ?\Etsy\Resources\EconomicOperator {
    return self::request(
      "PATCH",
      "/application/shops/{$shop_id}/economic-operators/{$economic_operator_id}",
      "EconomicOperator",
      $data
    );
  }

  /**
   * Saves updates to the current economic operator.
   *
   * @param array $data
   * @return \Etsy\Resources\EconomicOperator
   */
  public function save(
    ?array $data = null
  ): \Etsy\Resources\EconomicOperator {
    if(!$data) {
      $data = $this->getSaveData(true);
    }
    if(count($data) == 0) {
      return $this;
    }
    return $this->updateRequest(
      "/application/shops/{$this->shop_id}/economic-operators/{$this->economic_operator_id}",
      $data,
      "PATCH"
    );
  }
}
