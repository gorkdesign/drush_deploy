<?php
/**
 *
 */
namespace Drush;
class Command {
  private static $annotations = array();
  private static $functions = array();
  public static $before = array();
  public static $after = array();
  public static $tasks = array();
  public $classname = NULL;

  public function className() {
    if (empty($this->class_name)) {
      $class = get_called_class();
      $ref = new \ReflectionClass($this->class);
      $this->class_name = $this->ref->getShortName();
    }
  }

  public function runCommand($obj, $cmd) {
    $before = drush_get_option('before', array());
    $after = drush_get_option('after', array());
    $short_cmd = $cmd;
    if (strpos($cmd, 'deploy_') === 0) {
      $short_cmd = substr($cmd, 7);
    }

    // See if there are any before tasks to run before calling the command callback.
    if (isset($before[$short_cmd])) {
      foreach($before[$short_cmd] as $task) {
        $task($obj);
      }
    }

    // Call command callback.
    $ret = $obj->{$cmd}();

    // Call any after tasks.
    if (isset($after[$short_cmd])) {
      foreach($after[$short_cmd] as $task) {
        $task($obj);
      }
    }

    return $ret;
  }

  static function getCommands() {
    $class = get_called_class();
    $ref = new \ReflectionClass($class);
    $blah = self::$functions;
    $class_name = $ref->getShortName();
    $annotations = self::getClassAnnotations($ref);

    $commands = array();
     foreach($annotations[$class_name] as $method_name => $a) {
       if (isset($a['command'])) {
         $commands[] = $method_name;
       }
     }
     return $commands;
   }

  static function getFunctions($reset = FALSE) {
    if (empty(self::$functions) || $reset === FALSE) {
      $functions = get_defined_functions();
      self::$functions = $functions['user'];
    }
    return self::$functions;
  }

  static function getTasks() {
    $class = get_called_class();
    $ref = new \ReflectionClass($class);
    $namespace = $ref->getNamespaceName();
    $namespace_match = strtolower($namespace);
    $functions = self::getFunctions();
    $tasks = array();
    foreach ($functions as $f) {
      if (strpos($f, $namespace_match) === 0) {
        $tasks[] = $f;
      }
    }
    $annotations = self::getFunctionAnnotations($tasks);
    foreach ($annotations as $function => $a) {
      if (isset($a['task'])) {
        self::$tasks[] = $function;
      }
      if (isset($a['before'])) {
        self::$before[$a['before']][] = $function;
      }
      if (isset($a['after'])) {
        foreach ($a['after'] as $after_command) {
          self::$before[$after_command][] = $function;
        }
      }
    }
    return $tasks;
  }

  private static function getFunctionAnnotations($functions) {
    foreach ($functions as $f) {
      $ref = new \ReflectionFunction($f);
      if (!isset(self::$annotations[$f])) {
        self::$annotations[$f] = self::parseAnnotations($ref->getDocComment());
      }
    }
    return self::$annotations;
  }

  private static function getClassAnnotations(\ReflectionClass $ref) {
    $class_name = $ref->getShortName();

    if (!isset(self::$annotations[$class_name])) self::$annotations[$class_name] = array();

    foreach ($ref->getMethods() as $method) {
      $method_name = $method->getName();
      if (!isset(self::$annotations[$class_name][$method_name])) {
        self::$annotations[$class_name][$method_name] = self::parseAnnotations($method->getDocComment());
      }
    }
    return self::$annotations;
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
