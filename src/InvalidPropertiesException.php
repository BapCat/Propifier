<?php namespace BapCat\Propifier;

use Exception;
use ReflectionMethod;

class InvalidPropertyException extends Exception {
  private $property;
  
  public function __construct(ReflectionMethod $property) {
    parent::__construct("Property [{$property->name}] has an invalid number of arguments.");
    $this->property = $property;
  }
  
  public function getProperty() {
    return $this->property;
  }
}
