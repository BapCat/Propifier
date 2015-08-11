<?php namespace BapCat\Propifier;

use Exception;

/**
 * Indicates an accessor or mutator does not exist for a given property
 * 
 * @author    Corey Frenette
 * @copyright Copyright (c) 2015, BapCat
 */
class NoSuchPropertyException extends Exception {
  /**
   * The property that does not exist
   * 
   * @var ReflectionMethod
   */
  private $property;
  
  /**
   * Constructor
   * 
   * @param ReflectionMethod $property The property that does not exist
   */
  public function __construct($property) {
    parent::__construct("Property [$property] does not exist.");
    $this->property = $property;
  }
  
  /**
   * Get the property that does not exist
   * 
   * @return ReflectionMethod The property that does not exist
   */
  public function getProperty() {
    return $this->property;
  }
}
