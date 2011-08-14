<?php
/**
 *
 */
namespace DrushDeploy;
class DrushCommand {
  private static $annotations = array();

  public function __construct() {
    $this->class = get_called_class();
    $this->ref = new \ReflectionClass($this->class);
    $this->class_name = $this->ref->getShortName();
  }

  static function getCommands() {
    $class = get_called_class();
    $ref = new \ReflectionClass($class);
    $class_name = $ref->getShortName();
    self::getAnnotations($ref);

    $commands = array();
    $annotations = self::$annotations;
     foreach($annotations[$class_name] as $method_name => $a) {
       if (isset($a['command'])) {
         $commands[] = $method_name;
       }
     }
     return $commands;
   }

  private static function getAnnotations(\ReflectionClass $ref) {
    $class_name = $ref->getShortName();

    if (!isset(self::$annotations[$class_name])) self::$annotations[$class_name] = array();

    foreach($ref->getMethods() as $method) {
      $method_name = $method->getName();
      if (!isset(self::$annotations[$class_name][$method_name])) {
        self::$annotations[$class_name][$method_name] = self::parseAnnotations($method->getDocComment());
      }
    }
  }

   /**
    * Stolen from phpunit.
    *
    * @param  string $docblock
    * @return array
    */
  private static function parseAnnotations($docblock) {
    $annotations = array();

    if (preg_match_all('/@(?P<name>[A-Za-z_-]+)(?:[ \t]+(?P<value>.*?))?[ \t]*\r?$/m', $docblock, $matches)) {
      $numMatches = count($matches[0]);

      for ($i = 0; $i < $numMatches; ++$i) {
        $annotations[$matches['name'][$i]][] = $matches['value'][$i];
      }
    }

    return $annotations;
  }
}
