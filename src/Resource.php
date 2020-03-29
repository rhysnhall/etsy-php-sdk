<?php

namespace Etsy;

/**
 * Base resource object.
 *
 * @author Rhys Hall hello@rhyshall.com
 */
class Resource {

  protected $_assocations = [];
  protected $_properties = [];

  public function __construct($properties = false) {
    $this->_properties = $properties ? $properties : new \stdClass;
  }

  public function __get($property) {
    // Check for any mutators. If one exists then we want to call that instead of directly getting the property.
    if(method_exists($this, $property) && isset($this->_properties[$property])) {
      return $this->$property();
    }
    // Return null for any property that is not set.
    if(!isset($this->_properties->$property)) {
      return null;
    }
    return $this->_properties->$property;
  }

  public function __set($property, $value) {
    $this->_propertoes->$property = $value;
  }

  // @TODO Return object as JSON
  public function __toString() {

  }

}
