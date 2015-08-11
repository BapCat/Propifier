<?php

class InvalidSet {
  use BapCat\Propifier\PropifierTrait;
  
  private $something = 'test';
  
  protected function setSomething($value, $a, $b) {
    $this->something = $value;
  }
}
