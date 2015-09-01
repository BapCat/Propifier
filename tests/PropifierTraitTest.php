<?php

require_once __DIR__ . '/stubs/Dummy.php';
require_once __DIR__ . '/stubs/GetOnly.php';
require_once __DIR__ . '/stubs/SetOnly.php';
require_once __DIR__ . '/stubs/ArrayAll.php';
require_once __DIR__ . '/stubs/ArrayGetOnly.php';
require_once __DIR__ . '/stubs/ArraySetOnly.php';
require_once __DIR__ . '/stubs/ArrayItrOnly.php';
require_once __DIR__ . '/stubs/ArrayItrAndGet.php';
require_once __DIR__ . '/stubs/ArrayItrAndSet.php';
require_once __DIR__ . '/stubs/Mismatch.php';
require_once __DIR__ . '/stubs/InvalidGet.php';
require_once __DIR__ . '/stubs/InvalidSet.php';
require_once __DIR__ . '/stubs/GetterCalledGetMethod.php';

use BapCat\Propifier\InvalidPropertyException;
use BapCat\Propifier\MismatchedPropertiesException;
use BapCat\Propifier\NoSuchPropertyException;

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
    $this->setExpectedException(NoSuchPropertyException::class);
    $value = new Dummy();
    $test = $value->asdf;
  }
  
  public function testSetDoesntExist() {
    $this->setExpectedException(NoSuchPropertyException::class);
    $value = new Dummy();
    $value->asdf = 'asdf';
  }
  
  public function testGetOnly() {
    $value = new GetOnly();
    $this->assertEquals('test', $value->something);
    
    $this->setExpectedException(NoSuchPropertyException::class);
    $value->something = '';
  }
  
  public function testSetOnly() {
    $value = new SetOnly();
    $value->something = '';
    
    $this->setExpectedException(NoSuchPropertyException::class);
    $a = $value->something;
  }
  
  public function testArrayGetOnly() {
    $value = new ArrayGetOnly();
    $this->assertEquals('test', $value->something['test']);
    
    $this->setExpectedException(NoSuchPropertyException::class);
    $value->something['test'] = '';
  }
  
  public function testArraySetOnly() {
    $value = new ArraySetOnly();
    $value->something['test'] = '';
    
    $this->setExpectedException(NoSuchPropertyException::class);
    $a = $value->something['test'];
  }
  
  public function testMismatchedPropertiesViaGet() {
    $value = new Mismatch();
    
    $this->setExpectedException(MismatchedPropertiesException::class);
    $a = $value->something;
  }
  
  public function testMismatchedPropertiesViaSet() {
    $value = new Mismatch();
    
    $this->setExpectedException(MismatchedPropertiesException::class);
    $value->something = '';
  }
  
  public function testGetterWithTooManyParams() {
    $value = new InvalidGet();
    
    $this->setExpectedException(InvalidPropertyException::class);
    $a = $value->something;
  }
  
  public function testSetterWithTooManyParams() {
    $value = new InvalidSet();
    
    $this->setExpectedException(InvalidPropertyException::class);
    $value->something = '';
  }
  
  // See issue #2
  public function testGetterCalledGetMethod() {
    $getMethod = new GetterCalledGetMethod();
    
    $this->assertSame('test', $getMethod->method);
  }
  
  public function testIteration() {
    $in = ['a' => 'b'];
    
    $itr = new ArrayItrOnly(['a' => 'b']);
    
    $out = [];
    foreach($itr->arr as $key => $val) {
      $out[$key] = $val;
    }
    
    $this->assertSame($in, $out);
  }
  
  public function testIterationAndAccessor() {
    $in = ['a' => 'b'];
    
    $itr = new ArrayItrAndGet(['a' => 'b']);
    
    $out = [];
    foreach($itr->arr as $key => $val) {
      $out[$key] = $val;
    }
    
    $this->assertSame($in, $out);
    $this->assertSame($in['a'], $itr->arr['a']);
    
    $this->setExpectedException(NoSuchPropertyException::class);
    $itr->arr['test'] = '';
  }
  
  public function testIterationAndMutator() {
    $in = ['a' => 'b'];
    
    $itr = new ArrayItrAndSet(['a' => 'b']);
    
    $itr->arr['a'] = 'test';
    
    $out = [];
    foreach($itr->arr as $key => $val) {
      $out[$key] = $val;
    }
    
    $this->assertSame(['a' => 'test'], $out);
    
    $this->setExpectedException(NoSuchPropertyException::class);
    $itr->arr['test'];
  }
  
  public function testIterationWithGetAndSet() {
    $in = ['a' => 'b'];
    
    $itr = new ArrayAll(['a' => 'b']);
    
    $out = [];
    foreach($itr->arr as $key => $val) {
      $out[$key] = $val;
    }
    
    $this->assertSame($in, $out);
    
    $itr->arr['test'] = 'test';
    
    $this->assertSame('test', $itr->arr['test']);
  }
  
  public function testIterationWithOnlyGetter() {
    $value = new ArrayGetOnly();
    
    $this->setExpectedException(NoSuchPropertyException::class);
    foreach($value->something as $val) {
      
    }
  }
  
  public function testIterationWithOnlySetter() {
    $value = new ArraySetOnly();
    
    $this->setExpectedException(NoSuchPropertyException::class);
    foreach($value->something as $val) {
      
    }
  }
}
