<?php

require_once __DIR__ . '/stubs/Dummy.php';
require_once __DIR__ . '/stubs/GetOnly.php';
require_once __DIR__ . '/stubs/SetOnly.php';
require_once __DIR__ . '/stubs/ArrayGetOnly.php';
require_once __DIR__ . '/stubs/ArraySetOnly.php';
require_once __DIR__ . '/stubs/Mismatch.php';
require_once __DIR__ . '/stubs/InvalidGet.php';
require_once __DIR__ . '/stubs/InvalidSet.php';
require_once __DIR__ . '/stubs/GetterCalledGetMethod.php';

class ValueTest extends PHPUnit_Framework_TestCase {
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
  
  public function testGetOnly() {
    $value = new GetOnly();
    $this->assertEquals('test', $value->something);
    
    $this->setExpectedException('BapCat\Propifier\NoSuchPropertyException');
    $value->something = '';
  }
  
  public function testSetOnly() {
    $value = new SetOnly();
    $value->something = '';
    
    $this->setExpectedException('BapCat\Propifier\NoSuchPropertyException');
    $a = $value->something;
  }
  
  public function testArrayGetOnly() {
    $value = new ArrayGetOnly();
    $this->assertEquals('test', $value->something['test']);
    
    $this->setExpectedException('BapCat\Propifier\NoSuchPropertyException');
    $value->something['test'] = '';
  }
  
  public function testArraySetOnly() {
    $value = new ArraySetOnly();
    $value->something['test'] = '';
    
    $this->setExpectedException('BapCat\Propifier\NoSuchPropertyException');
    $a = $value->something['test'];
  }
  
  public function testMismatchedPropertiesViaGet() {
    $value = new Mismatch();
    
    $this->setExpectedException('BapCat\Propifier\MismatchedPropertiesException');
    $a = $value->something;
  }
  
  public function testMismatchedPropertiesViaSet() {
    $value = new Mismatch();
    
    $this->setExpectedException('BapCat\Propifier\MismatchedPropertiesException');
    $value->something = '';
  }
  
  public function testGetterWithTooManyParams() {
    $value = new InvalidGet();
    
    $this->setExpectedException('BapCat\Propifier\InvalidPropertyException');
    $a = $value->something;
  }
  
  public function testSetterWithTooManyParams() {
    $value = new InvalidSet();
    
    $this->setExpectedException('BapCat\Propifier\InvalidPropertyException');
    $value->something = '';
  }
  
  // See issue #2
  public function testGetterCalledGetMethod() {
    $getMethod = new GetterCalledGetMethod();
    
    $this->assertSame('test', $getMethod->method);
  }
}
