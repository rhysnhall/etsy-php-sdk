<?php

namespace Etsy;

use Etsy\Etsy;
use Etsy\Utils\Request as RequestUtil;

/**
 * Holds an array of resources.
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
   * @var integer
   */
  public $count = 0;

  /**
   * @var array
   */
  public $data = [];

  /**
   * Constructor method for the collection.
   *
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
   * Paginate generator provides continued iteration against the Etsy limitations on number of records returned per call.
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
    return Etsy::getCollection($response, $this->resource);
  }

}
