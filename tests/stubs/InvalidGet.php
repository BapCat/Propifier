<?php

class InvalidGet {
  use BapCat\Propifier\PropifierTrait;
  
  private $something = 'test';
  
  protected function getSomething($a, $b) {
    return $this->something;
  }
}
