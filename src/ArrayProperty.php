<?php declare(strict_types = 1); namespace BapCat\Propifier;

use ArrayAccess;
use IteratorAggregate;
use ReflectionMethod;
use Traversable;

/**
 * A magic accessor/mutator for dealing with array properties
 *
 * @author     Corey Frenette
 * @copyright  Copyright (c) 2019, BapCat
 */
class ArrayProperty implements ArrayAccess, IteratorAggregate {
  /** @var  string  $name  The name of the property */
  private $name;

  /** @var  object  $obj  The object instance we are getting the properties from */
  private $obj;

  /** @var  ReflectionMethod|null  $get  The accessor */
  private $get;

  /** @var  ReflectionMethod|null  $set  The mutator */
  private $set;

  /** @var  ReflectionMethod|null  $iterator  The iterator */
  private $iterator;

  /**
   * @param  ReflectionMethod|null  $get       The accessor if one is defined, otherwise null
   * @param  ReflectionMethod|null  $set       The mutator if one is defined, otherwise null
   * @param  ReflectionMethod|null  $iterator  The iterator if one is defined, otherwise null
   */
  public function __construct(ReflectionMethod $get = null, ReflectionMethod $set = null, ReflectionMethod $iterator = null) {
    $this->get      = $get;
    $this->set      = $set;
    $this->iterator = $iterator;

    if($get !== null) {
      $this->name = $get->name;
      $get->setAccessible(true);
    }

    if($set !== null) {
      $this->name = $set->name;
      $set->setAccessible(true);
    }

    if($iterator !== null) {
      $this->name = $iterator->name;
      $iterator->setAccessible(true);
    }
  }

  /**
   * Update the object so we are accessing properties on the correct object.
   * This must be done because ArrayProperty objects are shared between all
   * instances of a given class.
   *
   * @param  object  $obj  The new object
   *
   * @return  void
   */
  public function this($obj): void {
    $this->obj = $obj;
  }

  /**
   * Executes the accessor for a given array property
   *
   * @throws  NoSuchPropertyException  If there is no accessor for this array property
   *
   * @param  mixed  $offset  The index into the array to access
   *
   * @return  mixed  The return value of the accessor
   */
  public function offsetGet($offset): mixed {
    if($this->get !== null) {
      return $this->get->invoke($this->obj, $offset);
    }

    throw new NoSuchPropertyException($this->name);
  }

  /**
   * Executes the mutator for a given array property
   *
   * @throws  NoSuchPropertyException  If there is no mutator for this array property
   *
   * @param  mixed  $offset  The index into the array to mutate
   * @param  mixed  $value   The new value
   */
  public function offsetSet($offset, $value): void {
    if($this->set !== null) {
      $this->set->invoke($this->obj, $offset, $value);
      return;
    }

    throw new NoSuchPropertyException($this->name);
  }

  /**
   * @TODO
   *
   * @param  mixed  $offset
   *
   * @return  bool
   */
  public function offsetExists($offset): bool {
    return false;
  }

  /**
   * @TODO
   *
   * @param  mixed  $offset
   *
   * @return  void
   */
  public function offsetUnset($offset): void {

  }

  /**
   * Returns the iterator for the array
   *
   * @throws  NoSuchPropertyException  If there is no iterator for the array
   *
   * @return  Traversable  The iterator
   */
  public function getIterator(): Traversable {
    if($this->iterator !== null) {
      return $this->iterator->invoke($this->obj);
    }

    throw new NoSuchPropertyException($this->name);
  }
}
