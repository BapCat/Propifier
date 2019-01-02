<?php namespace BapCat\Propifier;

use Exception;

/**
 * Indicates an accessor or mutator does not exist for a given property
 *
 * @author    Corey Frenette
 * @copyright Copyright (c) 2019, BapCat
 */
class NoSuchPropertyException extends Exception {
  /**
   * @var  string   $property  The property that does not exist
   */
  private $property;

  /**
   * @param  string  $property  The property that does not exist
   */
  public function __construct(string $property) {
    parent::__construct("Property [$property] does not exist.");
    $this->property = $property;
  }

  /**
   * Get the property that does not exist
   *
   * @return  string  The property that does not exist
   */
  public function getProperty(): string {
    return $this->property;
  }
}
