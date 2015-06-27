<?php

require_once __DIR__ . '/stubs/Dummy.php';

class ValueTest extends PHPUnit_Framework_Testcase {
  public function testMagicProperties() {
    $value = new Dummy();
    $value->something = 'test';
    $this->assertEquals('test', $value->something);
  }
  
  public function testArrayProperties() {
    $value = new Dummy();
    $value->array[0] = 100;
    $this->assertEquals(100, $value->array[0]);
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
