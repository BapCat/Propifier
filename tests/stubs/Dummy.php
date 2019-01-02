<?php declare(strict_types = 1);

use BapCat\Propifier\PropifierTrait;

/**
 * @property  mixed  $something
 * @property  array  $array
 */
class Dummy {
  use PropifierTrait;

  /** @var  mixed  $something */
  private $something;

  /** @var  array  $array  */
  private $array = [1, 2, 3, 4];

  protected function getSomething() {
    return $this->something;
  }

  protected function setSomething($val): void {
    $this->something = $val;
  }

  protected function getArray($index) {
    return $this->array[$index];
  }

  protected function setArray($index, $val): void {
    $this->array[$index] = $val;
  }
}
