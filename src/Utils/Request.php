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
    $data = [];
    foreach ($params as $key => $value) {
      if(in_array($key, ['image', 'video', 'file'])) {
        $data[] = [
          'name' => $key,
          'contents' => fopen($value, 'r')
        ];
      } else {
        $data[] = [
          'name' => $key,
          'contents' => $value
        ];
      }
    }
    
    return $data;
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
