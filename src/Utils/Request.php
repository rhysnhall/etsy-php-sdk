<?php

namespace Etsy\Utils;

/**
 *  HTTP request utilities.
 *
 * @author Rhys Hall hello@rhyshall.com
 */
class Request {

  const ALLOWED_PARAMETERS = ['limit', 'offset', 'page', 'includes', 'min_created', 'max_created', 'sort_order', 'shop_name'];

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

  /**
   * Formats the includes parameter for Etsy.
   *
   * @param array $params
   * @return array
   */
  public static function formatAssociations($params) {
    if(isset($params['includes'])) {
      $includes = $params['includes'];
      if(is_string($includes)) {
        $includes = explode(',', $includes);
      }

      $ucfirst = function($string) {
        return ucfirst(trim($string));
      };
      // Ensure first character is uppercase. Etsy associations are case sensitive.
      $includes = implode(',',
        array_map($ucfirst, $includes)
      );
      // If using dot notation for nesting replace dots with forward slash and ensure the first character of each nested association is uppercase.
      $includes = str_replace('.', '/', $includes);
      $includes = implode('/',
        array_map($ucfirst, explode('/', $includes))
      );

      // This prevents issues on paginated queries without having to target the includes param outside of this method. No sane API would use commas in a url.
      $params['includes'] = str_replace(['%252C', '%2C'], ',', $includes);
    }
    return $params;
  }

}
