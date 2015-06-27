<?php

class Dummy {
  use BapCat\Propifier\PropifierTrait;
  
  private $something;
  private $array = [1, 2, 3, 4];
  
  protected function getSomething() {
    return $this->something;
  }
  
  protected function setSomething($val) {
    $this->something = $val;
  }
  
  protected function getArray($index) {
    return $this->array[$index];
  }
  
  protected function setArray($index, $val) {
    $this->array[$index] = $val;
  }
}
