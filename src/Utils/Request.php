<?php

namespace Etsy\Utils;

/**
 *  HTTP request utilities.
 *
 * @author Rhys Hall hello@rhyshall.com
 */
class Request {

  const ALLOWED_PARAMETERS = ['limit', 'offset', 'page', 'includes'];

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
   * @return array
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

}
