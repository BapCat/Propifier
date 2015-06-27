<?php namespace BapCat\Propifier;

use ArrayAccess;
use ReflectionMethod;

class ArrayProperty implements ArrayAccess {
  private $obj;
  private $get;
  private $set;
  
  public function __construct($obj, ReflectionMethod $get = null, ReflectionMethod $set = null) {
    $this->obj = $obj;
    $this->get = $get;
    $this->set = $set;
    
    if($get !== null) {
      $get->setAccessible(true);
    }
    
    if($set !== null) {
      $set->setAccessible(true);
    }
  }
  
  public function offsetGet($offset) {
    if($this->get !== null) {
      $ret = $this->get->invoke($this->obj, $offset);
      return $ret;
    }
  }
  
  public function offsetSet($offset, $value) {
    if($this->set !== null) {
      return $this->set->invoke($this->obj, $offset, $value);
    }
  }
  
  public function offsetExists($offset) {
    
  }
  
  public function offsetUnset($offset) {
    
  }
}
