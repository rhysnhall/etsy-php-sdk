<?php

namespace Etsy;

use Etsy\Etsy;
use Etsy\Utils\Request as RequestUtil;

/**
 * Holds a collection of resources.
 *
 * @author Rhys Hall hello@rhyshall.com
 */
class Collection {

  /**
   * @var string
   */
  protected $resource;

  /**
   * @var string
   */
  protected $url;

  /**
   * @var array
   */
  protected $params = [];

  /**
   * @var integer|false
   */
  public $next_page = null;

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
   * @param string $url
   * @return void
   */
  public function __construct($resource, $url) {
    $this->resource = $resource;
    $url = explode('?', $url);
    $this->url = $url[0];
    if(isset($url[1])) {
      $this->params = RequestUtil::getParamaters($url[1]);
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
   * @return void
   */
  public function paginate() {
    $page = $this;
    while(true) {
      foreach($page->data as $resource) {
        yield $resource;
      }
      if(is_null($page->next_page)) {
        break;
      }
      $page = $page->nextPage();
    }
  }

  /**
   * Gets the next page (subset of records).
   *
   * @return Collection
   */
  private function nextPage() {
    $this->params['page'] = $this->next_page;
    $response = Etsy::makeRequest(
      'GET',
      $this->url,
      $this->params
    );
    $collection = Etsy::getCollection($response, $this->resource);
    if(count($this->_append)) {
      $collection->append($this->_append);
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
