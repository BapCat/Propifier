<?php namespace BapCat\Propifier;

use ICanBoogie\Inflector;
use ReflectionClass;
use ReflectionMethod;

/**
 * Turns regular accessors and mutators into real properties
 * 
 * @author    Corey Frenette
 * @copyright Copyright (c) 2015, BapCat
 */
trait PropifierTrait {
  /**
   * A property cache shared between all instances
   * 
   * @var array
   */
  private static $method_map = [];
  
  /**
   * Builds and caches the properties for a given object, if not already cached
   * 
   * @param $obj The object to cache
   */
  private static function buildDependencies($obj) {
    $name = get_class($obj);
    
    if(!array_key_exists($name, self::$method_map)) {
      $class = new ReflectionClass($obj);
      $methods = $class->getMethods(ReflectionMethod::IS_PROTECTED);
      $properties = self::filterProperties($methods);
      
      $inflector = Inflector::get();
      
      $method_map = self::remapProperties([$inflector, 'underscore'], $properties);
      
      self::$method_map[$name] = self::extractProperties($obj, $method_map);
    }
  }
  
  /**
   * Filters out all methods that don't match the property signatures
   * 
   * @param  array $methods An array of methods to filter
   * 
   * @return array The methods after filtering out non-properties
   */
  private static function filterProperties(array $methods) {
    return array_filter($methods, function(ReflectionMethod $method) {
      return
        (strlen($method->name) > 3) && (
          (strpos($method->name, 'get') === 0) ||
          (strpos($method->name, 'set') === 0)
        )
      ;
    });
  }
  
  /**
   * Transforms property names and pairs them up where applicable
   * 
   * @param  callable $inflector  An instance of Inflector to transform property names
   * @param  array    $properties The properties to transform
   * 
   * @return array The properties after transformation and pairing
   */
  private static function remapProperties(callable $inflector, array $properties) {
    $mapped = [];
    
    foreach($properties as $property) {
      $prop_name = $inflector(substr($property->name, 3));
      
      if(!isset($mapped[$prop_name])) {
        $mapped[$prop_name] = ['get' => null, 'set' => null];
      }
      
      $mapped[$prop_name][substr($property->name, 0, 3)] = $property;
    }
    
    return $mapped;
  }
  
  /**
   * Verifies and builds accessors/mutators for each property
   * 
   * @throws MismatchedPropertiesException If one property is an array property and the other isn't
   * @throws InvalidPropertyException      If a property has an invalid number of arguments
   * 
   * @param  object $obj        The object we're building the properties for
   * @param  array  $properties The properties to transform
   * 
   * @return array The executable properties for the given object
   */
  private static function extractProperties($obj, array $properties) {
    $extracted = [];
    
    foreach($properties as $name => $property) {
      $hasGet = $property['get'] !== null;
      $hasSet = $property['set'] !== null;
      
      if($hasGet && $hasSet) {
        if(
          $property['get']->getNumberOfParameters() == 0 &&
          $property['set']->getNumberOfParameters() == 1
        ) {
          // Regular get and set
          $extracted[$name] = ['get' => $property['get']->name, 'set' => $property['set']->name];
        } elseif(
          $property['get']->getNumberOfParameters() == 1 &&
          $property['set']->getNumberOfParameters() == 2
        ) {
          // Array get and set
          $array_property = new ArrayProperty($property['get'], $property['set']);
          $extracted[$name] = ['get' => $array_property, 'set' => $array_property];
        } else {
          throw new MismatchedPropertiesException($property['get'], $property['set']);
        }
      } elseif($hasGet) {
        if($property['get']->getNumberOfParameters() == 0) {
          // Regular get
          $extracted[$name] = ['get' => $property['get']->name, 'set' => null];
        } elseif($property['get']->getNumberOfParameters() == 1) {
          // Array get
          $extracted[$name] = ['get' => new ArrayProperty($property['get'], null), 'set' => null];
        } else {
          throw new InvalidPropertyException($property['get']);
        }
      } elseif($hasSet) {
        if($property['set']->getNumberOfParameters() == 1) {
          // Regular set
          $extracted[$name] = ['get' => null, 'set' => $property['set']->name];
        } elseif($property['set']->getNumberOfParameters() == 2) {
          // Array set
          $extracted[$name] = ['get' => new ArrayProperty(null, $property['set']), 'set' => null];
        } else {
          throw new InvalidPropertyException($property['set']);
        }
      }
    }
    
    return $extracted;
  }
  
  /**
   * Gets the accessor or mutator for a given property.  This method will build the cache if necessary.
   * 
   * @throws NoSuchPropertyException If there is no accessor or mutator for the property
   * 
   * @param  string $name   The name of the property
   * @param  string $prefix The type of property (ie. get, set)
   * 
   * @return array The executable property
   */
  private function getMethod($name, $prefix) {
    self::buildDependencies($this);
    
    if(isset(self::$method_map[get_class($this)][$name][$prefix])) {
      return self::$method_map[get_class($this)][$name][$prefix];
    }
    
    throw new NoSuchPropertyException($name);
  }
  
  /**
   * Executes the accessor for a given property
   * 
   * @param  string $name The name of the property
   * 
   * @return mixed The value of the property
   */
  public function __get($name) {
    $method = $this->getMethod($name, 'get');
    
    if($method instanceof ArrayProperty) {
      $method->this($this);
      return $method;
    }
    
    return $this->$method();
  }
  
  /**
   * Executes the mutator for a given property
   * 
   * @param  string $name  The name of the property
   * @param  mixed  $value The value to set the property to
   */
  public function __set($name, $value) {
    $method = $this->getMethod($name, 'set');
    
    $this->$method($value);
  }
}
