<?php namespace BapCat\Propifier;

use ArrayAccess;
use ReflectionMethod;

/**
 * A magic accessor/mutator for dealing with array properties
 * 
 * @author    Corey Frenette
 * @copyright Copyright (c) 2015, BapCat
 */
class ArrayProperty implements ArrayAccess {
  /**
   * The object instance we are getting the properties from
   * 
   * @var object
   */
  private $obj;
  
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
   * @param ReflectionMethod $get The accessor if one is defined, otherwise null
   * @param ReflectionMethod $set The mutator if one is defined, otherwise null
   */
  public function __construct(ReflectionMethod $get = null, ReflectionMethod $set = null) {
    $this->get = $get;
    $this->set = $set;
    
    if($get !== null) { $get->setAccessible(true); }
    if($set !== null) { $set->setAccessible(true); }
  }
  
  /**
   * Update the object so we are accessing properties on the correct object.
   * This must be done because ArrayProperty objects are shared between all
   * instances of a given class.
   * 
   * @param object $obj The new object
   */
  public function this($obj) {
    $this->obj = $obj;
  }
  
  /**
   * Executes the accessor for a given array property
   * 
   * @throws NoSuchPropertyException If there is no accessor for this array property
   * 
   * @param mixed $offset The index into the array to access
   */
  public function offsetGet($offset) {
    if($this->get !== null) {
      return $this->get->invoke($this->obj, $offset);
    }
    
    throw new NoSuchPropertyException($this->set->name);
  }
  
  /**
   * Executes the mutator for a given array property
   * 
   * @throws NoSuchPropertyException If there is no mutator for this array property
   * 
   * @param mixed $offset The index into the array to mutate
   * @param mixed $value  The new value
   */
  public function offsetSet($offset, $value) {
    if($this->set !== null) {
      return $this->set->invoke($this->obj, $offset, $value);
    }
    
    throw new NoSuchPropertyException($this->get->name);
  }
  
  /**
   * @TODO
   */
  public function offsetExists($offset) {
    
  }
  
  /**
   * @TODO
   */
  public function offsetUnset($offset) {
    
  }
}
