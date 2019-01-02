<?php declare(strict_types = 1);

use BapCat\Propifier\PropifierTrait;

/**
 * @property-read  string  $something
 */
class GetOnly {
  use PropifierTrait;

  /** @var  string  $something */
  private $something = 'test';

  protected function getSomething(): string {
    return $this->something;
  }
}
