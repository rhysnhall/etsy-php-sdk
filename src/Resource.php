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
  protected $_properties = [];

  /**
   * Constructor method for the Resource class.
   *
   * @param array $properties
   * @return void
   */
  public function __construct($properties = false) {
    if($properties) {
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
    if(method_exists($this, $find) && isset($properties[$find])) {
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
   * @return Resource
   */
  protected function updateRequest(string $url, array $data, $method = "PUT") {
    $result = $this->request(
        $method,
        $url,
        basename(str_replace('\\', '/', get_class($this))),
        $data
      );
    // Update the existing properties.
    $properties = get_object_vars($result)['_properties'];
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
  protected function deleteRequest(string $url, array $data = []) {
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
   * @return Collection
   */
  protected function request(
    string $method,
    string $url,
    string $resource,
    array $params = []
  ) {
    $response = Etsy::$client->{strtolower($method)}(
      $url,
      $params
    );
    return Etsy::getResource($response, $resource);
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
    $array = [];
    $properties = get_object_vars($this)['_properties'];
    foreach($properties as $property => $value) {
      if(is_object($value)) {
        if($value instanceof Resource) {
          $array[$property] = $value->toArray();
        }
        else {
          $array[$property] = get_object_vars($value);
        }
      }
      else {
        $array[$property] = $value;
      }
    }
    return $array;
  }

}
