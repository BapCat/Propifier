<?php declare(strict_types = 1); namespace BapCat\Propifier;

use ICanBoogie\Inflector;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;

use function get_class;
use function strlen;

/**
 * Turns regular accessors and mutators into real properties
 *
 * @author    Corey Frenette
 * @copyright Copyright (c) 2019, BapCat
 */
trait PropifierTrait {
  /** @var  string[][][]|ArrayProperty[][][]|null[][][]  $method_map  A property cache shared between all instances */
  private static $method_map = [];

  /**
   * Builds and caches the properties for a given object, if not already cached
   *
   * @throws  MismatchedPropertiesException  If one property is an array property and the other isn't
   * @throws  InvalidPropertyException       If a property has an invalid number of arguments
   *
   * @param  object  $obj  The object to cache
   *
   * @return  void
   */
  private static function buildDependencies($obj): void {
    $name = get_class($obj);

    if(!array_key_exists($name, self::$method_map)) {
      try {
        $class = new ReflectionClass($obj);
      } catch(ReflectionException $e) {
        throw new RuntimeException("This shouldn't be possible", 0, $e);
      }

      $methods = $class->getMethods(ReflectionMethod::IS_PROTECTED);
      $properties = self::filterProperties($methods);

      $inflector = Inflector::get();

      $method_map = self::remapProperties([$inflector, 'underscore'], $properties);

      self::$method_map[$name] = self::extractProperties($method_map);
    }
  }

  /**
   * Filters out all methods that don't match the property signatures
   *
   * @param  ReflectionMethod[]  $methods  An array of methods to filter
   *
   * @return  ReflectionMethod[]  The methods after filtering out non-properties
   */
  private static function filterProperties(array $methods): array {
    return array_filter($methods, function(ReflectionMethod $method) {
      return
        (strlen($method->name) > 3) && (
          (strpos($method->name, 'get') === 0) ||
          (strpos($method->name, 'set') === 0) ||
          (strpos($method->name, 'itr') === 0)
        )
      ;
    });
  }

  /**
   * Transforms property names and pairs them up where applicable
   *
   * @param  callable            $inflector   An instance of Inflector to transform property names
   * @param  ReflectionMethod[]  $properties  The properties to transform
   *
   * @return  ReflectionMethod[][]  The properties after transformation and pairing
   */
  private static function remapProperties(callable $inflector, array $properties): array {
    $mapped = [];

    foreach($properties as $property) {
      $prop_name = $inflector(substr($property->name, 3));

      if(!isset($mapped[$prop_name])) {
        $mapped[$prop_name] = ['get' => null, 'set' => null, 'itr' => null];
      }

      $mapped[$prop_name][substr($property->name, 0, 3)] = $property;
    }

    return $mapped;
  }

  /**
   * Verifies and builds accessors/mutators for each property
   *
   * @throws  MismatchedPropertiesException  If one property is an array property and the other isn't
   * @throws  InvalidPropertyException       If a property has an invalid number of arguments
   *
   * @param  ReflectionMethod[][]  $properties  The properties to transform
   *
   * @return  string[][][]|ArrayProperty[][][]|null[][][]  The executable properties for the given object
   */
  private static function extractProperties(array $properties): array {
    $extracted = [];

    foreach($properties as $name => $property) {
      $hasGet = $property['get'] !== null;
      $hasSet = $property['set'] !== null;

      if($hasGet && $hasSet) {
        if(
          $property['get']->getNumberOfParameters() === 0 &&
          $property['set']->getNumberOfParameters() === 1
        ) {
          // Regular get and set
          $extracted[$name] = ['get' => $property['get']->name, 'set' => $property['set']->name];
        } elseif(
          $property['get']->getNumberOfParameters() === 1 &&
          $property['set']->getNumberOfParameters() === 2
        ) {
          // Array get and set
          $array_property = new ArrayProperty($property['get'], $property['set'], $property['itr']);
          $extracted[$name] = ['get' => $array_property, 'set' => $array_property];
        } else {
          throw new MismatchedPropertiesException($property['get'], $property['set']);
        }
      } elseif($hasGet) {
        if($property['get']->getNumberOfParameters() === 0) {
          // Regular get
          $extracted[$name] = ['get' => $property['get']->name, 'set' => null];
        } elseif($property['get']->getNumberOfParameters() === 1) {
          // Array get
          $extracted[$name] = ['get' => new ArrayProperty($property['get'], null, $property['itr']), 'set' => null];
        } else {
          throw new InvalidPropertyException($property['get']);
        }
      } elseif($hasSet) {
        if($property['set']->getNumberOfParameters() === 1) {
          // Regular set
          $extracted[$name] = ['get' => null, 'set' => $property['set']->name];
        } elseif($property['set']->getNumberOfParameters() === 2) {
          // Array set
          $extracted[$name] = ['get' => new ArrayProperty(null, $property['set'], $property['itr']), 'set' => null];
        } else {
          throw new InvalidPropertyException($property['set']);
        }
      } else {
        $extracted[$name] = ['get' => new ArrayProperty(null, null, $property['itr']), 'set' => null];
      }
    }

    return $extracted;
  }

  /**
   * Gets the accessor or mutator for a given property.  This method will build the cache if necessary.
   *
   * @throws  NoSuchPropertyException        If there is no accessor or mutator for the property
   * @throws  MismatchedPropertiesException  If one property is an array property and the other isn't
   * @throws  InvalidPropertyException       If a property has an invalid number of arguments
   *
   * @param  string  $name    The name of the property
   * @param  string  $prefix  The type of property (ie. get, set)
   *
   * @return  string|ArrayProperty|null  The executable property
   */
  private function _propifier_getMethod(string $name, string $prefix) {
    if($this->_propifier_hasMethod($name, $prefix)) {
      return self::$method_map[get_class($this)][$name][$prefix];
    }

    throw new NoSuchPropertyException($name);
  }

  /**
   * Checks if a given property exists.  This method will build the cache if necessary.
   *
   * @throws  MismatchedPropertiesException  If one property is an array property and the other isn't
   * @throws  InvalidPropertyException       If a property has an invalid number of arguments
   *
   * @param  string  $name    The name of the property
   * @param  string  $prefix  The type of property (ie. get, set)
   *
   * @return  bool  True if the property exists, false otherwise
   */
  private function _propifier_hasMethod(string $name, string $prefix): bool {
    self::buildDependencies($this);

    return isset(self::$method_map[get_class($this)][$name][$prefix]);
  }

  /**
   * Executes the accessor for a given property
   *
   * @throws  NoSuchPropertyException
   * @throws  MismatchedPropertiesException  If one property is an array property and the other isn't
   * @throws  InvalidPropertyException       If a property has an invalid number of arguments
   *
   * @param  string  $name  The name of the property
   *
   * @return  mixed  The value of the property
   */
  public function __get(string $name) {
    $method = $this->_propifier_getMethod($name, 'get');

    if($method instanceof ArrayProperty) {
      $method->this($this);
      return $method;
    }

    return $this->$method();
  }

  /**
   * Executes the mutator for a given property
   *
   * @throws  NoSuchPropertyException
   * @throws  MismatchedPropertiesException  If one property is an array property and the other isn't
   * @throws  InvalidPropertyException       If a property has an invalid number of arguments
   *
   *
   * @param  string  $name   The name of the property
   * @param  mixed   $value  The value to set the property to
   *
   * @return  void
   */
  public function __set(string $name, $value): void {
    $method = $this->_propifier_getMethod($name, 'set');

    $this->$method($value);
  }

  /**
   * Checks if a property is set
   *
   * @throws  MismatchedPropertiesException  If one property is an array property and the other isn't
   * @throws  InvalidPropertyException       If a property has an invalid number of arguments
   *
   * @param  string  $name  The name of the property
   *
   * @return  bool  True if the property exists, false otherwise
   */
  public function __isset(string $name): bool {
    return $this->_propifier_hasMethod($name, 'get');
  }
}
