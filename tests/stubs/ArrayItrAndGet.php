<?php

class ArrayItrAndGet {
  use BapCat\Propifier\PropifierTrait;
  
  private $arr;
  
  public function __construct(array $arr) {
    $this->arr = $arr;
  }
  
  protected function getArr($index) {
    return $this->arr[$index];
  }
  
  protected function itrArr() {
    return new ArrayIterator($this->arr);
  }
}
