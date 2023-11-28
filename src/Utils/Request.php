<?php

namespace Etsy\Utils;

/**
 *  HTTP request utilities.
 *
 * @author Rhys Hall hello@rhyshall.com
 */
class Request {

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
   * Prepares any files in the POST data. Expects a path for files.
   *
   * @param array $params
   * @return array
   */
  public static function prepareFile(array $params) {
    if(!isset($params['image']) && !isset($params['file'])) {
      return false;
    }
    if($other_params = array_diff_key($params, array_flip(['image', 'file']))) {
      $other_params = array_map(function ($key) use($other_params){
        return [
          'name' => $key,
          'contents' => $other_params[$key]
        ];
      }, array_keys($other_params));
    }
    $type = isset($params['image']) ? 'image' : 'file';
    return array_merge($other_params ?? [],
      [[
        'name' => $type,
        'contents' => fopen($params[$type], 'r')
      ]]
    );
  }

  /**
   * Returns a query string as an array.
   *
   * @param string $query
   * @return array
   */
  public static function getParamaters($query) {
    parse_str($query, $params);
    return $params;
  }

}
