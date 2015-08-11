<?php

class Mismatch {
  use BapCat\Propifier\PropifierTrait;
  
  private $something;
  
  protected function getSomething($index) {
    return $this->something;
  }
  
  protected function setSomething($val) {
    $this->something = $val;
  }
}
