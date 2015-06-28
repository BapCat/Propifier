<?php namespace BapCat\Propifier;

use Exception;
use ReflectionMethod;

class MismatchedPropertiesException extends Exception {
  private $get;
  private $set;
  
  public function __construct(ReflectionMethod $get = null, ReflectionMethod $set = null) {
    $name = '';
    
    if($get !== null) {
      $name = $get->name;
    } else {
      $name = $set->name;
    }
    
    parent::__construct("Declaration of property [$name] is inconsistent.");
    
    $this->get = $get;
    $this->set = $set;
  }
  
  public function getGet() {
    return $this->get;
  }
  
  public function getSet() {
    return $this->set;
  }
}
