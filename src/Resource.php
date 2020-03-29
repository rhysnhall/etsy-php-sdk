<?php

namespace Etsy;

/**
 * Base resource object.
 *
 * @author Rhys Hall hello@rhyshall.com
 */
class Resource {

  /**
   * @var array
   */
  protected $_assocations = [];

  /**
   * @var array
   */
  protected $_properties = [];

  public function __construct($properties = false) {
    $this->_properties = $properties
      ? $this->linkAssociations($properties)
      : new \stdClass;
  }

  public function __get($property) {
    // Change the case of all properties to lower case. We want to match all cases. I.e. shop & Shop are both valid.
    $find = strtolower($property);
    $properties = array_change_key_case((array)$this->_properties);
    // Check for any mutators. If one exists then we want to call that instead of directly getting the property.
    if(method_exists($this, $property) && isset($properties[$find])) {
      return $this->$property();
    }

    // If this property is not part of the _properties object and it does exist directly on this object then we want to return it.
    if(isset($this->$property) && !isset($properties[$find])) {
      return $this->$property;
    }

    // Return null for any property that is not set.
    if(!isset($properties[$find])) {
      return null;
    }
    return $properties[$find];
  }

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
  public function linkAssociations($properties) {
    foreach($this->_assocations as $association => $object) {
      if(isset($properties->$association)) {
        $resource = __NAMESPACE__."\\Resources\\{$object}";
        $properties->$association = new $resource($properties->$association);
      }
    }
    return $properties;
  }

  // @TODO Return object as JSON
  public function __toString() {

  }

}
