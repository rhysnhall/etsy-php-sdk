<?php

namespace Etsy;

use Etsy\Etsy;
use Etsy\Utils\Request as RequestUtil;
use Etsy\Exception\SdkException;

/**
 * Holds a collection of resources.
 *
 * @author Rhys Hall hello@rhyshall.com
 */
class Collection {

  const PAGINATION_SUPPORT = [
    "Shop", "Review", "Listing", "Receipt", "Transaction", "LedgerEntry"
  ];

  /**
   * @var string
   */
  protected $resource;

  /**
   * @var string
   */
  protected $uri = false;

  /**
   * @var array
   */
  protected $params = [];

  /**
   * @var array
   */
  protected $_append = [];

  /**
   * @var array
   */
  public $data = [];

  /**
   * Constructor method for the collection.
   *
   * @param string $resource
   * @param string $uri
   * @return void
   */
  public function __construct($resource, $uri = false) {
    $this->resource = $resource;
    if($uri) {
      $uri = explode('?', $uri);
      $this->uri = $uri[0];
      if(isset($uri[1])) {
        $this->params = RequestUtil::getParamaters($uri[1]);
      }
    }
  }

  /**
   * Returns only the first result. Primarily used for fetching single resources.
   *
   * @return Etsy\Resource
   */
  public function first() {
    if(!count($this->data)) {
      return false;
    }
    return $this->data[0];
  }

  /**
   * Returns the number of resources within the collection.
   *
   * @return integer
   */
  public function count() {
    return count($this->data);
  }

  /**
   * Appends properties to each resource in the collection.
   *
   * @param array $data
   * @return Collection
   */
  public function append($data) {
    array_map(function($resource) use ($data) {
      foreach($data as $property => $value) {
        $resource->{$property} = $value;
      }
    }, $this->data);
    $this->_append = $data;
    return $this;
  }


  /**
   * Paginate generator provides continued iteration against the Etsy limitations on number of records returned per call.
   *
   * @param integer $results
   * @return void
   */
  public function paginate($results = 100) {
    // Limit max results to 500.
    if($results > 500) {
      $results = 500;
    }
    if(!in_array($this->resource, self::PAGINATION_SUPPORT)) {
      throw new SdkException("The {$this->resource} resource does not support pagination.");
    }
    $page = $this;
    $count = 0;
    while(true) {
      foreach($page->data as $resource) {
        yield $resource;
      }
      $count += count($page->data);
      if(!($page->next_page ?? true)
        || $count >= $results) {
        break;
      }
      if(!$page = $page->nextPage()) {
        break;
      }
    }
  }

  /**
   * Gets the next page (subset of records).
   *
   * @return Collection
   */
  private function nextPage() {
    $limit = $this->params['limit'] ?? 25;
    $this->params['offset'] = ($this->params['offset'] ?? 0) + $limit;
    $response = Etsy::$client->get(
      $this->uri,
      $this->params
    );
    $collection = Etsy::getResource($response, $this->resource);
    if(!$collection) {
      $collection->next_page = false;
      return false;
    }
    if(count($this->_append)) {
      $collection->append($this->_append);
    }
    if(count($collection->data) < $limit) {
      $collection->next_page = false;
    }
    return $collection;
  }

  /**
   * Returns the collections resources as an array of JSON strings.
   *
   * @return array
   */
  public function toJson() {
    return array_map(function($resource) {
      return $resource->toJson();
    }, $this->data);
  }

}
