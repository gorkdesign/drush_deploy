<?php
/**
 * @file
 *
 * @author Mark Sonnabaum (http://drupal.org/user/75278)
 *
 */

require 'upstream.inc';

/**
 * Ipmlementation of hook_drush_help().
 */
function deploy_drush_help($section) {
}

/**
 * Implementation of hook_drush_command().
 */
function deploy_drush_command() {
  $items = array();

  $items['deploy'] = array(
    'description' => '',
    'arguments' => array(
    ),
    'options' => array(
    ),
    'examples' => array(
    ),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUSH,
    'config' => 'deploy',
  );
  spl_autoload_register('drush_autoload_class');

  foreach(\DrushDeploy\Deploy::getCommands() as $command) {
    $items['deploy-' . $command] = array(
      'description' => '',
      'arguments' => array(),
      'options' => array(),
      'bootstrap' => DRUSH_BOOTSTRAP_DRUSH, // No bootstrap at all.
    );
  }

  foreach ($items as $command => &$item) {
    $item['callback'] = 'drush_deploy_callback';
    $item['callback arguments'] = array($command);
  }

  return $items;
}

/**
 * Ipmlementation of hook_drush_init().
 */
function deploy_drush_init() {
  // Load deploy config files.
  foreach (drush_context_names() as $context) {
    drush_load_config_file($context, _drush_config_file($context, 'deploy'));
  }
}

function deploy_task() {
  $args = func_get_args();
  $name = array_shift($args);
  if ($args[count($args) - 1] instanceof Closure) {
    $work = array_pop($args);
  }
  else {
    $work = null;
  }
  DrushDeploy\Deploy::$tasks[$name] = $work;
}

function deploy_before($before_task, $task) {
  DrushDeploy\Deploy::$before[$before_task][] = $task;
}

function deploy_after($after_task, $task) {
  DrushDeploy\Deploy::$after[$after_task][] = $task;
}

/**
 * Implementation of drush_hook_COMMAND().
 */
function drush_deploy_callback($command, $alias = NULL) {
  if (empty($alias)) {
    drush_set_error('DRUSH_DEPLOY_ERROR', dt("Must specify a site alias or group of site aliases!"));
    return;
  }
  $alias_settings = drush_sitealias_get_record($alias);
  $method = strpos($command, 'deploy-') === 0 ?  substr($command, 7) : $command;
  $sites = array();
  if (isset($alias_settings['site-list'])) {
    foreach ($alias_settings['site-list'] as $alias) {
      $sites[] = drush_sitealias_get_record($alias);
    }
  }
  else {
    $sites[] = $alias_settings;
  }

  $deploy = new \DrushDeploy\Deploy($sites);
  $blah = drush_deploy_run_command($deploy, $method);
  drush_print($blah);
}

function drush_deploy_run_command($obj, $cmd) {
  $before = DrushDeploy\Deploy::$before;
  $after = DrushDeploy\Deploy::$after;
  $short_cmd = $cmd;
  if (strpos($cmd, 'deploy_') === 0) {
    $short_cmd = substr($cmd, 7);
  }

  // See if there are any before tasks to run before calling the command callback.
  if (isset($before[$short_cmd])) {
    foreach($before[$short_cmd] as $task) {
      $task_closure = DrushDeploy\Deploy::$tasks[$task];
      $task_closure($obj);
    }
  }

  // Call command callback.
  $ret = $obj->{$cmd}();

  // Call any after tasks.
  if (isset($after[$short_cmd])) {
    foreach($after[$short_cmd] as $task) {
      $task_closure = DrushDeploy\Deploy::$tasks[$task];
      $task_closure($obj);
    }
  }

  return $ret;
}

function drush_deploy_transaction(&$obj = NULL, $commands) {
  if ($obj) {
    foreach ($commands as $i => $cmd) {
      try {
        drush_deploy_run_command($obj, $cmd);
      }
      catch (CommandException $e) {
        foreach ($commands as $ri => $rcmd) {
          if (is_callable(array($obj, $rcmd . '_rollback'))) {
            drush_deploy_run_command($obj, $rcmd . '_rollback');
            //$obj->{$rcmd . '_rollback'}();
          }
          if ($ri >= $i) break;
          $ri++;
        }
        break;
      }
    }
  }
}

/**
 * Just a copy of the PSR-0 autoloader. This should probably go in
 * drush core eventually.
 *
 * @param $className
 * @return void
 */
function drush_autoload_class($className) {
  $className = ltrim($className, '\\');
  $fileName  = '';
  $namespace = '';
  if ($lastNsPos = strripos($className, '\\')) {
    $namespace = substr($className, 0, $lastNsPos);
    $className = substr($className, $lastNsPos + 1);
    $fileName  = dirname(__FILE__) . '/' . str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
  }
  $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
  if (file_exists($fileName)) {
    require $fileName;
  }
}

