<?php

class Dummy {
  use BapCat\Propifier\PropifierTrait;
  
  private $something;
  
  protected function getSomething() {
    return $this->something;
  }
  
  protected function setSomething($val) {
    $this->something = $val;
  }
}
