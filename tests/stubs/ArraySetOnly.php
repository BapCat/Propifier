<?php

class ArraySetOnly {
  use BapCat\Propifier\PropifierTrait;
  
  private $something = ['test'];
  
  protected function setSomething($index, $value) {
    $this->something[$index] = $value;
  }
}
