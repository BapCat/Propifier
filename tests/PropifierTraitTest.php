<?php

require_once __DIR__ . '/stubs/Dummy.php';

class ValueTest extends PHPUnit_Framework_Testcase {
  public function testMagicProperties() {
    $value = new Dummy();
    $value->something = 'test';
    $this->assertEquals('test', $value->something);
  }
  
  public function testGetDoesntExist() {
    $this->setExpectedException('BapCat\Propifier\NoSuchPropertyException');
    $value = new Dummy();
    $test = $value->asdf;
  }
  
  public function testSetDoesntExist() {
    $this->setExpectedException('BapCat\Propifier\NoSuchPropertyException');
    $value = new Dummy();
    $value->asdf = 'asdf';
  }
}
