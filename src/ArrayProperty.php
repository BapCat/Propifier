<?php namespace BapCat\Propifier;

use ArrayAccess;
use ReflectionMethod;

class ArrayProperty implements ArrayAccess {
  private $obj;
  private $get;
  private $set;
  
  public function __construct(ReflectionMethod $get = null, ReflectionMethod $set = null) {
    $this->get = $get;
    $this->set = $set;
    
    if($get !== null) {
      $get->setAccessible(true);
    }
    
    if($set !== null) {
      $set->setAccessible(true);
    }
  }
  
  public function this($obj) {
    $this->obj = $obj;
  }
  
  public function offsetGet($offset) {
    if($this->get !== null) {
      return $this->get->invoke($this->obj, $offset);
    }
    
    throw new NoSuchPropertyException($this->set->name);
  }
  
  public function offsetSet($offset, $value) {
    if($this->set !== null) {
      return $this->set->invoke($this->obj, $offset, $value);
    }
    
    throw new NoSuchPropertyException($this->get->name);
  }
  
  public function offsetExists($offset) {
    
  }
  
  public function offsetUnset($offset) {
    
  }
}
