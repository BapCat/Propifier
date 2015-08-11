<?php

class ArrayGetOnly {
  use BapCat\Propifier\PropifierTrait;
  
  private $something = ['test' => 'test'];
  
  protected function getSomething($index) {
    return $this->something[$index];
  }
}
