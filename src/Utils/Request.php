<?php

namespace Etsy\Utils;

use Etsy\Exception\RequestException;
use Psr\Http\Message\StreamInterface;

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
    if(isset($params['image'])) {
      $type = 'image';
    }
    else if(isset($params['file'])) {
      $type ='file';
    }
    else if(isset($params['video'])) {
      $type = 'video';
    }
    else {
      return false;
    }
    $data[] = [
      'name' => $type,
      'contents' => self::openFile($params[$type])
    ];
    foreach ($params as $key => $value) {
      if ($key == $type) continue;
      $data[] = [
        'name' => $key,
        'contents' => $value
      ];
    }
    return $data;
  }

  /**
   * Opens a file for upload. Accepts a path, an open stream, or an array with
   * the file's 'contents'.
   *
   * @param mixed $file
   * @return mixed
   * @throws RequestException If the path can't be read.
   */
  private static function openFile($file) {
    if(is_array($file) && array_key_exists('contents', $file)) {
      return $file['contents'];
    }
    if(is_resource($file) || $file instanceof StreamInterface) {
      return $file;
    }
    if(!is_string($file)) {
      throw new RequestException("Can't read the file to upload: expected a path, stream or ['contents' => ...], got " . gettype($file) . ".");
    }
    // Local paths are checked first. URLs and other stream wrappers (when
    // allow_url_fopen permits) are opened directly.
    $isLocal = !preg_match('#^[a-z][a-z0-9+.-]*://#i', $file) || stripos($file, 'file://') === 0;
    $handle = (!$isLocal || (is_file($file) && is_readable($file))) ? @fopen($file, 'r') : false;
    if($handle === false) {
      throw new RequestException("Can't read the file to upload: {$file}");
    }
    return $handle;
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
