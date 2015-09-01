<?php

class ArrayItrOnly {
  use BapCat\Propifier\PropifierTrait;
  
  private $arr;
  
  public function __construct(array $arr) {
    $this->arr = $arr;
  }
  
  protected function itrArr() {
    return new ArrayIterator($this->arr);
  }
}
