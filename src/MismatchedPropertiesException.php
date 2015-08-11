<?php namespace BapCat\Propifier;

use Exception;
use ReflectionMethod;

/**
 * Indicates a property has mismatched arguments in the accessor and mutator
 * 
 * @author    Corey Frenette
 * @copyright Copyright (c) 2015, BapCat
 */
class MismatchedPropertiesException extends Exception {
  /**
   * The accessor
   * 
   * @var ReflectionMethod (nullable)
   */
  private $get;
  
  /**
   * The mutator
   * 
   * @var ReflectionMethod (nullable)
   */
  private $set;
  
  /**
   * Constructor
   * 
   * @param ReflectionMethod $get The accessor
   * @param ReflectionMethod $set The mutator
   */
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
  
  /**
   * Get the accessor
   * 
   * @return ReflectionMethod The accessor
   */
  public function getGet() {
    return $this->get;
  }
  
  /**
   * Get the mutator
   * 
   * @return ReflectionMethod The mutator
   */
  public function getSet() {
    return $this->set;
  }
}
