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
      
      $inflector = Inflector::get();
      
      self::$method_map[$name] = [
        'get' => self::remapMethods([$inflector, 'underscore'], $methods, 'get'),
        'set' => self::remapMethods([$inflector, 'underscore'], $methods, 'set')
      ];
    }
  }
  
  private static function remapMethods(callable $inflector, array $methods, $prefix) {
    $properties = array_filter($methods, function(ReflectionMethod $method) use($prefix) {
      return (strlen($method->name) > strlen($prefix)) && (strpos($method->name, $prefix) === 0);
    });
    
    $mapped = [];
    
    foreach($properties as $index => $method) {
      $prop_name = $inflector(substr($method->name, strlen($prefix)));
      $mapped[$prop_name] = $method->name;
    }
    
    return $mapped;
  }
  
  private function getMethod($name, $prefix) {
    self::buildDependencies($this);
    
    if(array_key_exists($name, self::$method_map[get_class($this)][$prefix])) {
      return self::$method_map[get_class($this)][$prefix][$name];
    }
    
    throw new NoSuchPropertyException();
  }
  
  public function __get($name) {
    $method = $this->getMethod($name, 'get');
    return $this->$method();
  }
  
  public function __set($name, $value) {
    $method = $this->getMethod($name, 'set');
    $this->$method($value);
  }
}
