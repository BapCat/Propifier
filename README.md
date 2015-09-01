[![Build Status](https://travis-ci.org/BapCat/Propifier.svg?branch=1.0.1)](https://travis-ci.org/BapCat/Propifier)
[![Coverage Status](https://coveralls.io/repos/BapCat/Propifier/badge.svg?branch=1.0.1)](https://coveralls.io/r/BapCat/Propifier?branch=1.0.1)
[![License](https://img.shields.io/packagist/l/BapCat/Propifier.svg)](https://img.shields.io/packagist/l/BapCat/Propifier.svg)

# Propifier
A trait that adds real object-oriented property support to PHP.

## Installation

### Composer
[Composer](https://getcomposer.org/) is the recommended method of installation for all BapCat packages.

```
$ composer require bapcat/propifier
```

### GitHub

BapCat packages may be downloaded from [GitHub](https://github.com/BapCat/Propifier/).

## The Problem With PHP "Properties"
Anyone coming from another language like .NET will probably be accustomed to defining properties (accessors and mutators) to
control access to private variables of a class.  Unfortunately, PHP lacks support for this useful feature.  There are several
workarounds...

#### Public Properties

```php
class Foo {
  public $a;
  public $b;
}
```

Using public properties is the easiest, but completely lacks encapsulation.  You have no control over who can change the
internal state of your object.

### `__get` and `__set`

```php
class Foo {
  private $a;
  private $b;
  
  public function __get($name) {
    if(isset($this->$name)) {
      return $this->$name;
    }
    
    throw new Exception('Invalid property!');
  }
  
  public function __set($name, $value) {
    switch($name) {
      case 'a':
        $this->a = $value;
      break;
      
      case 'b':
        throw new Exception('b is read-only!');
    }
    
    throw new Exception('Invalid property!');
  }
}
```

Using PHP's late binding support gives you control over what can be read from and written to your object, but sacrifices
readability, efficiency, and type hinting.  It's also not possible to control read/write access to arrays this way without
using other workarounds.

```php
...
  private $array = [];
...
  public function __get($name) {
    if(isset($this->$name)) {
      return $this->$name;
    }
  }
  
  public function __set($name, $value) {
    throw new Exception('You can\'t set me!');
  }
...

$foo = new FooWithArrayThatCantBeSet();

$foo->array['a'] = 'Test'; // Note: no exception
echo $foo->array['a']; // -> 'Test'
```

#### Getters and Setters

```php
class Foo {
  private $a;
  private $b;
  private $array = [];
  
  public function getA() {
    return $this->a;
  }
  
  public function setA(A $a) {
    $this->a = $a;
  }
  
  public function getB() {
    return $this->b;
  }
  
  public function getOnlyOneArrayValue($index) {
    return $this->array[$index];
  }
}
```

Using Java-style getters and setters is one of the best ways to implement properties in PHP, but still has flaws.  It is
very verbose:

```php
$a = $foo->getA(); // rather than $foo->a
```

You must also forgo using array access syntax to access array properties:

```php
$one_array_value = $foo->getOnlyOneArrayValue(1); // rather than $foo->array[1]
```

## The Propifier Way

Propifier solves every one of these problems.

```php
class Foo {
  use \BapCat\Propifier\PropifierTrait;
  
  private $a;
  private $b;
  private $array = [];
  
  public function __construct() {
    $a = null;
    $b = new B();
    $array['test'] = 'Test';
  }
  
  protected function getA() {
    return $this->a;
  }
  
  protected function setA(A $a) { // Type hinting
    $this->a = $a;
  }
  
  protected function getB() {
    return $this->b;
  }
  
  // Controlled access
  //protected function setB(B $b) {
  //  $this->b = $b;
  //}
  
  // Propifier automatically detects arrays, and
  // allows array access when using the property
  protected function getArray($index) {
    return $this->array[$index];
  }
  
  // You can even define iterators to add foreach support
  protected function itrArray() {
    return new ArrayIterator($this->array);
  }
}
```

```php
$foo = new Foo();

echo $foo->a; // -> null
$foo->a = new A(); // $a == new instance of A

echo $foo->b; // -> instance of B
$foo->b = new B(); // exception

echo $foo->array['test']; // -> 'Test'
$foo->array = []; // exception
$foo->array[1] = 'Test?'; // exception

foreach($foo->array as $key => $value) {
  // ...
}
```

#### Efficiency

Propifier will make you more efficient at writing code that matters, and unlike similar solutions, Propifier is
designed from the ground up to be fast.  It figures everything out at the start, and maintains a static mapping of all
of your objects' properties so using them is always fast.
