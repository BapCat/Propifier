<?php

class GetOnly {
  use BapCat\Propifier\PropifierTrait;
  
  private $something = 'test';
  
  protected function getSomething() {
    return $this->something;
  }
}
