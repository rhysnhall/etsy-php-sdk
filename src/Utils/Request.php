<?php

namespace Etsy\Utils;

/**
 *  HTTP request utilities.
 *
 * @author Rhys Hall hello@rhyshall.com
 */
class Request {

  const ALLOWED_PARAMETERS = ['limit', 'offset', 'page', 'includes', 'min_created', 'max_created', 'sort_order'];

  /**
   * Removes any invalid parameters.
   *
   * @param array $params
   * @return array
   */
  public static function validateParameters(array $params) {
    $allowed = self::ALLOWED_PARAMETERS;
    $prepared = [];
    foreach($params as $key => $value) {
      if(in_array($key, $allowed)) {
        $prepared[$key] = $value;
      }
    }
    return $prepared;
  }

  /**
   * Prepares the request query parameters.
   *
   * @param array $params
   * @return string
   */
  public static function prepareParameters(array $params) {
    $query = http_build_query($params);
    return $query;
  }

  /**
   * Prepares the POST data.
   *
   * @param array $params
   * @return array
   */
  public static function preparePostData(array $params) {
    return $params;
  }

  /**
   * Prepares any files in the POST data. Expects a path for files.
   *
   * @param array $params
   * @return array
   */
  public static function prepareFile(array $params) {
    if(!isset($params['image']) && !isset($params['file'])) {
      return false;
    }
    $type = isset($params['image']) ? 'image' : 'file';
    return [
      [
        'name' => $type,
        'contents' => fopen($params[$type], 'r')
      ]
    ];
  }

  /**
   * Returns a query string as an array.
   *
   * @param string $query
   * @return array
   */
  public static function getParamaters($query) {
    $params = [];
    foreach(explode('&', $query) as $param) {
      @list($key, $value) = explode('=', $param);
      $params[$key] = $value;
    }
    return $params;
  }

}
