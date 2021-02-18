<?php declare(strict_types = 1);

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
use PHPUnit\Framework\TestCase;

class PropifierTraitTest extends TestCase {
  public function testMagicProperties(): void {
    $value = new Dummy();
    $value->something = 'test';
    $this->assertEquals('test', $value->something);
  }

  public function testArrayProperties(): void {
    $value = new Dummy();
    $value->array[0] = 100;
    $this->assertEquals(100, $value->array[0]);
  }

  public function testGetDoesntExist(): void {
    $value = new Dummy();

    $this->expectException(NoSuchPropertyException::class);
    $value->asdf;
  }

  public function testSetDoesntExist(): void {
    $value = new Dummy();

    $this->expectException(NoSuchPropertyException::class);
    $value->asdf = 'asdf';
  }

  public function testGetOnly(): void {
    $value = new GetOnly();
    $this->assertEquals('test', $value->something);

    $this->expectException(NoSuchPropertyException::class);
    $value->something = '';
  }

  public function testSetOnly(): void {
    $value = new SetOnly();
    $value->something = '';

    $this->expectException(NoSuchPropertyException::class);
    $value->something;
  }

  public function testArrayGetOnly(): void {
    $value = new ArrayGetOnly();
    $this->assertEquals('test', $value->something['test']);

    $this->expectException(NoSuchPropertyException::class);
    $value->something['test'] = '';
  }

  public function testArraySetOnly(): void {
    $value = new ArraySetOnly();
    $value->something['test'] = '';

    $this->expectException(NoSuchPropertyException::class);
    $value->something['test'];
  }

  public function testMismatchedPropertiesViaGet(): void {
    $value = new Mismatch();

    $this->expectException(MismatchedPropertiesException::class);
    $value->something;
  }

  public function testMismatchedPropertiesViaSet(): void {
    $value = new Mismatch();

    $this->expectException(MismatchedPropertiesException::class);
    $value->something = '';
  }

  public function testGetterWithTooManyParams(): void {
    $value = new InvalidGet();

    $this->expectException(InvalidPropertyException::class);
    $value->something;
  }

  public function testSetterWithTooManyParams(): void {
    $value = new InvalidSet();

    $this->expectException(InvalidPropertyException::class);
    $value->something = '';
  }

  // See issue #2
  public function testGetterCalledGetMethod(): void {
    $getMethod = new GetterCalledGetMethod();

    $this->assertSame('test', $getMethod->method);
  }

  public function testIteration(): void {
    $in = ['a' => 'b'];

    $itr = new ArrayItrOnly(['a' => 'b']);

    $out = [];
    foreach($itr->arr as $key => $val) {
      $out[$key] = $val;
    }

    $this->assertSame($in, $out);
  }

  public function testIterationAndAccessor(): void {
    $in = ['a' => 'b'];

    $itr = new ArrayItrAndGet(['a' => 'b']);

    $out = [];
    foreach($itr->arr as $key => $val) {
      $out[$key] = $val;
    }

    $this->assertSame($in, $out);
    $this->assertSame($in['a'], $itr->arr['a']);

    $this->expectException(NoSuchPropertyException::class);
    $itr->arr['test'] = '';
  }

  public function testIterationAndMutator(): void {
    $in = ['a' => 'b'];

    $itr = new ArrayItrAndSet($in);

    $itr->arr['a'] = 'test';

    $out = [];
    foreach($itr->arr as $key => $val) {
      $out[$key] = $val;
    }

    $this->assertSame(['a' => 'test'], $out);

    $this->expectException(NoSuchPropertyException::class);
    $itr->arr['test'];
  }

  public function testIterationWithGetAndSet(): void {
    $in = ['a' => 'b'];

    $itr = new ArrayAll($in);

    $out = [];
    foreach($itr->arr as $key => $val) {
      $out[$key] = $val;
    }

    $this->assertSame($in, $out);

    $itr->arr['test'] = 'test';

    $this->assertSame('test', $itr->arr['test']);
  }

  public function testIterationWithOnlyGetter(): void {
    $value = new ArrayGetOnly();

    $this->expectException(NoSuchPropertyException::class);
    foreach($value->something as $val) {
      $this->assertSame('Fail if we get here', $val);
    }
  }

  public function testIterationWithOnlySetter(): void {
    $value = new ArraySetOnly();

    $this->expectException(NoSuchPropertyException::class);
    foreach($value->something as $val) {
      $this->assertSame('Fail if we get here', $val);
    }
  }

  public function testIsSet(): void {
    $obj = new GetOnly();

    $this->assertTrue(isset($obj->something));
    $this->assertNotEmpty($obj->something);
    $this->assertFalse(isset($obj->something_else));
    $this->assertTrue(empty($obj->something_else));
  }
}
