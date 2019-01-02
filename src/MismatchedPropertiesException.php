<?php declare(strict_types = 1); namespace BapCat\Propifier;

use Exception;
use ReflectionMethod;

/**
 * Indicates a property has mismatched arguments in the accessor and mutator
 *
 * @author    Corey Frenette
 * @copyright Copyright (c) 2019, BapCat
 */
class MismatchedPropertiesException extends Exception {
  /** @var  ReflectionMethod|null  $get  The accessor */
  private $get;

  /** @var  ReflectionMethod|null  $set  The mutator */
  private $set;

  /**
   * @param  ReflectionMethod|null  $get  The accessor
   * @param  ReflectionMethod|null  $set  The mutator
   */
  public function __construct(ReflectionMethod $get = null, ReflectionMethod $set = null) {
    if($get !== null) {
      $name = $get->name;
    } elseif($set !== null) {
      $name = $set->name;
    } else {
      $name = '';
    }

    parent::__construct("Declaration of property [$name] is inconsistent.");

    $this->get = $get;
    $this->set = $set;
  }

  /**
   * Get the accessor
   *
   * @return  ReflectionMethod  The accessor
   */
  public function getGet(): ReflectionMethod {
    return $this->get;
  }

  /**
   * Get the mutator
   *
   * @return  ReflectionMethod  The mutator
   */
  public function getSet(): ReflectionMethod {
    return $this->set;
  }
}
