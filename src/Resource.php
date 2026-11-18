<?php

namespace Etsy;

use Etsy\Etsy;

/**
 * Base resource object.
 *
 * @author Rhys Hall hello@rhyshall.com
 */
class Resource {

  /**
   * @var array
   */
  protected $_associations = [];

  /**
   * @var array
   */
  protected $_rename = [];

  /**
   * @var array
   */
  protected $_saveable = [];

  /**
   * @var array
   */
  protected $_properties = [];

  /**
   * @var array
   */
  private $_originalState = [];

  /**
   * Constructor method for the Resource class.
   *
   * @param array $properties
   * @return void
   */
  public function __construct($properties = false) {
    if($properties) {
      $this->_properties = $properties;
      $this->_originalState = $this->toArray();
      $properties = $this->renameProperties($properties);
      $properties = $this->linkAssociations($properties);
    }
    else {
      $properties = new \stdClass;
    }
    $this->_properties = $properties;
  }

  /**
   * Gets a property from the _properties attribute.
   *
   * @param integer|string $property
   * @return mixed
   */
  public function __get($property) {
    // Change the case of all properties to lower case. We want to match all cases. I.e. shop & Shop are both valid.
    $find = strtolower($property);
    $properties = array_change_key_case((array)$this->_properties);
    // Check for any mutators. If one exists then we want to call that instead of directly getting the property.
    // Associations that were included in the response are returned as is, rather than fetched again.
    $associations = array_change_key_case($this->_associations);
    if(method_exists($this, $find) && isset($properties[$find]) && !isset($associations[$find])) {
      return $this->$property();
    }
    // Return null for any property that is not set.
    if(!isset($properties[$find])) {
      return null;
    }
    return $properties[$find];
  }

  /**
   * Sets a property on the resource by adding it to the _properties attribute.
   *
   * @param integer|string $property
   * @param mixed $value
   * @return void
   */
  public function __set($property, $value) {
    // To prevent double ups we need to check for font cases.
    foreach((array)$this->_properties as $k => $v) {
      if(strtolower($k) == strtolower($property)) {
        $property = $k;
        continue;
      }
    }
    $this->_properties->$property = $value;
  }

  /**
   * Links the associations to their respective object classes.
   *
   * @param \stdClass $properties
   * @return \stdClass
   */
  protected function linkAssociations($properties) {
    foreach($this->_associations as $association => $resource) {
      $association = strtolower($association);
      if(isset($properties->$association)) {
        if(is_array($properties->$association)) {
          $properties->$association = Etsy::createCollectionResources(
              $properties->$association,
              $resource
            );
        }
        else {
          $properties->$association = Etsy::createResource(
              $properties->$association,
              $resource
            );
        }
      }
    }
    return $properties;
  }

  /**
   * Renames properties to cater for Etsy's inconsistent bizzare naming decisions.
   *
   * @param \stdClass $properties
   * @return \stdClass
   */
  protected function renameProperties($properties) {
    foreach($this->_rename as $expecting => $new) {
      if(isset($properties->$expecting)) {
        $properties->$new = $properties->$expecting;
        unset($properties->$expecting);
      }
    }
    return $properties;
  }

  /**
   * Performs a request and updates the current resource with the return properties. Will perform a PUT request by default.
   *
   * @param string $url
   * @param array $data
   * @param string $method
   * @param array $options Request options. Pass ['json' => true] to send the body as JSON.
   * @return Resource
   */
  protected function updateRequest(string $url, array $data, $method = "PUT", array $options = []) {
    $result = $this->request(
        $method,
        $url,
        basename(str_replace('\\', '/', get_class($this))),
        $data,
        $options
      );
    // Update the existing properties.
    $properties = get_object_vars($result)['_properties'];
    $this->_originalState = $result->_originalState;
    foreach($properties as $property => $value) {
      if(isset($this->_properties->{$property})) {
        $this->_properties->{$property} = $value;
      }
    }
    return $this;
  }

  /**
   * Performs a DELETE request with the Etsy API. Either returns true or false
   * regardless of if the resource cannot be found or is invalid.
   *
   * @param string $url
   * @param string $data
   * @return boolean
   */
  protected static function deleteRequest(string $url, array $data = []) {
    $response = Etsy::$client->delete(
        $url,
        $data
      );
    return !isset($response->error);
  }

  /**
   * Makes a request to the Etsy API and returns a collection.
   *
   * @param string $method
   * @param string $url
   * @param string $resource
   * @param array $params
   * @param array $options Request options. Pass ['json' => true] to send the body as JSON.
   * @return Collection
   */
  public static function request(
    string $method,
    string $url,
    string $resource,
    array $params = [],
    array $options = []
  ) {
    $response = Etsy::$client->{strtolower($method)}(
      $url,
      $params,
      $options
    );
    return Etsy::getResource($response, $resource);
  }

  /**
   * Makes a request that is expected to return multiple resources. Always
   * returns a collection: an empty one if Etsy returns a 404 (and the
   * 404_error option is off).
   *
   * @param string $method
   * @param string $url
   * @param string $resource
   * @param array $params
   * @param array $options
   * @return Collection
   */
  public static function requestCollection(
    string $method,
    string $url,
    string $resource,
    array $params = [],
    array $options = []
  ): Collection {
    $result = static::request($method, $url, $resource, $params, $options);
    if($result instanceof Collection) {
      return $result;
    }
    $collection = new Collection($resource, $url);
    $collection->next_page = false;
    return $collection;
  }

  /**
   * Returns the Resources properties as a JSON encoded object.
   *
   * @return string
   */
  public function toJson() {
    return json_encode($this->toArray());
  }

  /**
   * Returns the Resources properties as an array.
   *
   * @return array
   */
  public function toArray() {
    return static::valueToArray(get_object_vars($this)['_properties']);
  }

  /**
   * Recursively converts resources, collections and objects to arrays.
   *
   * @param mixed $value
   * @return mixed
   */
  protected static function valueToArray($value) {
    if($value instanceof Resource) {
      return $value->toArray();
    }
    if($value instanceof Collection) {
      $value = $value->data;
    }
    if(is_object($value)) {
      $value = get_object_vars($value);
    }
    if(is_array($value)) {
      return array_map([static::class, 'valueToArray'], $value);
    }
    return $value;
  }

  /**
   * Get the saveable data.
   * 
   * @return array
   */
  protected function getSaveData($patch = false) {
    if($patch) {
      $changed = $this->getChanged(
        $this->toArray(),
        $this->_originalState
      );
    }
    else {
      $changed = $this->toArray();
    }
    $data = [];
    foreach($this->_saveable as $key) {
      if(isset($changed[$key])) {
        $data[$key] = $changed[$key];
      }
    }
    return $data;
  }

  /**
   * Get the properties which have changed.
   * 
   * @param array $arrayOne
   * @param array @arrayTwo
   * @return array
   */
  private function getChanged(
    array $arrayOne, 
    array $arrayTwo
  ): array {
    $changed = [];
    foreach($arrayOne as $key => $value) {
      if(array_key_exists($key, $arrayTwo)) {
        if(is_array($value)) {
          $recursiveChanged = self::getChanged($arrayTwo[$key], $value);
          if(count($recursiveChanged)) {
            $changed[$key] = $recursiveChanged;
          }
        }
        else {
          if($value != $arrayTwo[$key]) {
            $changed[$key] = $value;
          }
        }
      }
      else {
        $changed[$key] = $value;
      }
    }
    return $changed;
  }

}
