<?php declare(strict_types = 1); namespace BapCat\Propifier;

use Exception;
use ReflectionMethod;

/**
 * Indicates a property has an incorrect number of parameters
 *
 * @author    Corey Frenette
 * @copyright Copyright (c) 2019, BapCat
 */
class InvalidPropertyException extends Exception {
  /** @var  ReflectionMethod  $property  The invalid property */
  private $property;

  /**
   * @param  ReflectionMethod  $property  The invalid property
   */
  public function __construct(ReflectionMethod $property) {
    parent::__construct("Property [{$property->name}] has an invalid number of arguments.");
    $this->property = $property;
  }

  /**
   * Get the invalid property
   *
   * @return  ReflectionMethod  The invalid property
   */
  public function getProperty(): ReflectionMethod {
    return $this->property;
  }
}
