<?php namespace BapCat\Propifier;

use ArrayAccess;
use IteratorAggregate;
use ReflectionMethod;

/**
 * A magic accessor/mutator for dealing with array properties
 * 
 * @author     Corey Frenette
 * @copyright  Copyright (c) 2015, BapCat
 */
class ArrayProperty implements ArrayAccess, IteratorAggregate {
  /**
   * The name of the property
   * 
   * @var  string
   */
  
  /**
   * The object instance we are getting the properties from
   * 
   * @var  object
   */
  private $obj;
  
  /**
   * The accessor
   * 
   * @var  ReflectionMethod (nullable)
   */
  private $get;
  
  /**
   * The mutator
   * 
   * @var  ReflectionMethod (nullable)
   */
  private $set;
  
  /**
   * The iterator
   * 
   * @var  ReflectionMethod (nullable)
   */
  private $iterator;
  
  /**
   * Constructor
   * 
   * @param  ReflectionMethod  $get       The accessor if one is defined, otherwise null
   * @param  ReflectionMethod  $set       The mutator if one is defined, otherwise null
   * @param  ReflectionMethod  $iterator  The iterator if one is defined, otherwise null
   */
  public function __construct(ReflectionMethod $get = null, ReflectionMethod $set = null, ReflectionMethod $iterator = null) {
    $this->get      = $get;
    $this->set      = $set;
    $this->iterator = $iterator;
    
    if($get !== null) {
      $this->name = $get->name;
      $get->setAccessible(true);
    }
    
    if($set !== null) {
      $this->name = $set->name;
      $set->setAccessible(true);
    }
    
    if($iterator !== null) {
      $this->name = $iterator->name;
      $iterator->setAccessible(true);
    }
  }
  
  /**
   * Update the object so we are accessing properties on the correct object.
   * This must be done because ArrayProperty objects are shared between all
   * instances of a given class.
   * 
   * @param  object  $obj  The new object
   */
  public function this($obj) {
    $this->obj = $obj;
  }
  
  /**
   * Executes the accessor for a given array property
   * 
   * @throws  NoSuchPropertyException  If there is no accessor for this array property
   * 
   * @param  mixed  $offset  The index into the array to access
   * 
   * @return  mixed  The return value of the accessor
   */
  public function offsetGet($offset) {
    if($this->get !== null) {
      return $this->get->invoke($this->obj, $offset);
    }
    
    throw new NoSuchPropertyException($this->name);
  }
  
  /**
   * Executes the mutator for a given array property
   * 
   * @throws  NoSuchPropertyException  If there is no mutator for this array property
   * 
   * @param  mixed  $offset  The index into the array to mutate
   * @param  mixed  $value   The new value
   */
  public function offsetSet($offset, $value) {
    if($this->set !== null) {
      $this->set->invoke($this->obj, $offset, $value);
      return;
    }
    
    throw new NoSuchPropertyException($this->name);
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
  
  /**
   * Returns the iterator for the array
   * 
   * @throws  NoSuchPropertyException  If there is no iterator for the array
   * 
   * @return  Traversable  The iterator
   */
  public function getIterator() {
    if($this->iterator !== null) {
      return $this->iterator->invoke($this->obj);
    }
    
    throw new NoSuchPropertyException($this->name);
  }
}
