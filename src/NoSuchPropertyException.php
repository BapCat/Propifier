<?php namespace BapCat\Propifier;

use Exception;

class NoSuchPropertyException extends Exception {
  private $property;
  
  public function __construct($property, $message = null, $code = 0, Exception $previous = null) {
    parent::__construct($message, $code, $previous);
    $this->property = $property;
  }
  
  public function getProperty() {
    return $this->property;
  }
}
