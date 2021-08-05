<?php

namespace Etsy;

use Etsy\OAuth\Client;
use Etsy\Exception\ApiException;

class Etsy {

  /**
   * @var string
   */
  protected $api_key;

  /**
   * @var string
   */
  protected $client_id;

  /**
   * @var Etsy\OAuth\Client
   */
  public static $client;

  /**
   * @var integer|string
   */
  protected $user;

  public function __construct(
    string $client_id,
    string $api_key
  ) {
    $this->client_id = $client_id;
    $this->api_key = $api_key;
    static::$client = new Client($client_id);
    static::$client->setApiKey($api_key);
  }

  /**
   * Returns a resource object from the request result.
   *
   * @param object $response
   * @param string $resource
   * @return mixed
   */
  public static function getResource(
    $response,
    string $resource
  ) {
    if(!$response || ($response->error ?? false)) {
      return null;
    }
    if(isset($response->results)) {
      return static::createCollection($response, $resource);
    }
    return static::createResource($response, $resource);
  }

  /**
   *
   */
  public static function createCollection(
    $response,
    string $resource
  ) {
    $collection = new Collection($resource, $response->uri);
    if(!count($response->results) || !isset($response->results)) {
      return $collection;
    }
    $collection->data = static::createCollectionResources(
      $response->results,
      $resource
    );
    return $collection;
  }

  /**
   * Creates an array of a single Etsy resource.
   *
   * @param array $records
   * @param string $resource
   * @return mixed
   */
  public static function createCollectionResources(array $records, string $resource) {
    $resource = __NAMESPACE__ . "\\Resources\\{$resource}";
    return array_map(function($record) use($resource) {
      return new $resource($record);
    }, $records);
  }

  /**
   * Creates a new Etsy resource.
   *
   * @param json $record
   * @param string $resource
   * @return mixed
   */
  public static function createResource(
    $record,
    string $resource
  ) {
    $resource = __NAMESPACE__ . "\\Resources\\{$resource}";
    return new $resource($record);
  }

  /**
   * Check to confirm connectivity to the Etsy API with an application
   *
   * @link https://developers.etsy.com/documentation/reference#operation/ping
   * @return integer|false
   */
  public function ping() {
    $response = static::$client->get("/application/openapi-ping");
    return $response->application_id ?? false;
  }

  /**
   * Only supports getting the user for who the current API KEY is associated with.
   *
   * @return Etsy\Resources\User
   */
  public function getUser() {
    $user_id = explode(".", $this->api_key)[0];
    $response = static::$client->get("/application/users/{$user_id}");
    return static::getResource($response, "User");
  }

  /**
   * Gets an Etsy shop. If no shop_id is specified the current user will be queried for an associated shop.
   *
   * @param int $shop_id
   * @return Etsy\Resources\Shop
   */
  public function getShop(
    int $shop_id = null
  ) {
    if(!$shop_id) {
      return $this->getUser()->getShop();
    }
    $response = static::$client->get("/application/shops/{$shop_id}");
    return static::getResource($response, "Shop");
  }

  /**
   * Search for shops using a keyword.
   *
   * @param string $keyword
   * @param array $params
   * @return Etsy\Collection[Etsy\Resources\Shop]
   */
  public function getShops($keyword, $params = []) {
    if(!strlen(trim($keyword))) {
      throw new ApiException("You must specify a keyword when searching for Etsy shops.");
    }
    $params['shop_name'] = $keyword;
    $response = static::$client->get(
      "/application/shops",
      $params
    );
    return static::getResource($response, "Shop");
  }

  /**
   * Retrieves the full hierarchy tree of seller taxonomy nodes.
   *
   * @return Etsy\Collection[Etsy\Resources\Taxonomy]
   */
  public function getSellerTaxonomy() {
    $response = static::$client->get(
      "/application/seller-taxonomy/nodes"
    );
    return static::getResource($response, "Taxonomy");
  }

  /**
   * Retrieves a list of available shipping carriers and the mail classes associated with them for a given country
   *
   * @param string $iso_code
   * @return Etsy\Collection[Etsy\Resources\ShippingCarrier]
   */
  public function getShippingCarriers($iso_code) {
    $response = static::$client->get(
      "/application/shipping-carriers",
      [
        "origin_country_iso" => $iso_code
      ]
    );
    return static::getResource($response, "ShippingCarrier");
  }

}
