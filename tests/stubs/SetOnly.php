<?php

class SetOnly {
  use BapCat\Propifier\PropifierTrait;
  
  private $something = 'test';
  
  protected function setSomething($value) {
    $this->something = $value;
  }
}
