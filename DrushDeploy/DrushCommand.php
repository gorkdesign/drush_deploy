<?php
/**
 *
 */
namespace DrushDeploy;
class DrushCommand {
  static function getCommands() {
    $class = get_called_class();
    $ref = new \ReflectionClass($class);
    $class_name = strtolower($ref->getShortName());
    $commands = array();
    foreach($ref->getMethods() as $method) {
      $method_name = $method->getName();
      $method_name = preg_replace("/([A-Z])/e", "strtolower('-$1')", $method->getName());
      if (strpos($method_name, $class_name) === 0) {
        $commands[] = $method_name;
      }
    }
    return $commands;
  }
}
