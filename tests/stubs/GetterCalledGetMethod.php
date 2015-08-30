<?php

class GetterCalledGetMethod {
  use BapCat\Propifier\PropifierTrait;
  
  private $method = 'test';
  
  protected function getMethod() {
    return $this->method;
  }
}
