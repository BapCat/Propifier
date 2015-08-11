<?php namespace BapCat\Propifier;

use ICanBoogie\Inflector;
use ReflectionClass;
use ReflectionMethod;

trait PropifierTrait {
  private static $method_map = [];
  
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
          $extracted[$name] = ['get' => $property['get']->name, 'set' => $property['set']->name];
        } elseif(
          $property['get']->getNumberOfParameters() == 1 &&
          $property['set']->getNumberOfParameters() == 2
        ) {
          $array_property = new ArrayProperty($property['get'], $property['set']);
          $extracted[$name] = ['get' => $array_property, 'set' => $array_property];
        } else {
          throw new MismatchedPropertiesException($property['get'], $property['set']);
        }
      } elseif($hasGet) {
        if($property['get']->getNumberOfParameters() == 0) {
          $extracted[$name] = ['get' => $property['get']->name, 'set' => null];
        } elseif($property['get']->getNumberOfParameters() == 1) {
          $extracted[$name] = ['get' => new ArrayProperty($property['get'], null), 'set' => null];
        } else {
          throw new InvalidPropertyException($property['get']);
        }
      } elseif($hasSet) {
        if($property['set']->getNumberOfParameters() == 1) {
          $extracted[$name] = ['get' => null, 'set' => $property['set']->name];
        } elseif($property['set']->getNumberOfParameters() == 2) {
          $extracted[$name] = ['get' => new ArrayProperty(null, $property['set']), 'set' => null];
        } else {
          throw new InvalidPropertyException($property['set']);
        }
      }
    }
    
    return $extracted;
  }
  
  private function getMethod($name, $prefix) {
    self::buildDependencies($this);
    
    if(isset(self::$method_map[get_class($this)][$name][$prefix])) {
      return self::$method_map[get_class($this)][$name][$prefix];
    }
    
    throw new NoSuchPropertyException($name);
  }
  
  public function __get($name) {
    $method = $this->getMethod($name, 'get');
    
    if($method instanceof ArrayProperty) {
      $method->this($this);
      return $method;
    }
    
    return $this->$method();
  }
  
  public function __set($name, $value) {
    $method = $this->getMethod($name, 'set');
    
    $this->$method($value);
  }
}
