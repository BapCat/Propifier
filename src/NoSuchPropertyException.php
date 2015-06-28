<?php namespace BapCat\Propifier;

use Exception;

class NoSuchPropertyException extends Exception {
  private $property;
  
  public function __construct($property) {
    parent::__construct("Property [$property] does not exist.");
    $this->property = $property;
  }
  
  public function getProperty() {
    return $this->property;
  }
}
