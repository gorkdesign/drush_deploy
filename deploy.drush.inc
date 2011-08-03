<?php
/**
 * @file
 *
 * @author Mark Sonnabaum (http://drupal.org/user/75278)
 *
 */

/**
 * Ipmlementation of hook_drush_init().
 */
function deploy_drush_init() {
  require 'deploy_helpers.inc';
  // Load config files specified in command hooks.
  foreach (drush_context_names() as $context) {
    drush_load_config_file($context, drush_deploy_load_config($context, 'deploy'));
  }
}

/**

/**
 * Ipmlementation of hook_drush_help().
 */
function deploy_drush_help($section) {
}


/**
 * Modified copy of _drush_config_file. This should go upstream.
 */
function drush_deploy_load_config($context, $prefix = NULL) {
  $configs = array();
  $config_file = $prefix ? $prefix . '.' . 'drushrc.php' : 'drushrc.php';

  // Did the user explicitly specify a config file?
  if ($config = drush_get_option(array('c', 'config'))) {
    if (is_dir($config)) {
      $config = $config . '/drushrc.php';
    }
    $configs['custom'] = $config;
  }

  if ($site_path = drush_get_context('DRUSH_DRUPAL_SITE_ROOT')) {
    $configs['site'] = $site_path . "/" . $config_file;
  }

  if ($drupal_root = drush_get_context('DRUSH_DRUPAL_ROOT')) {
    $configs['drupal'] = $drupal_root . '/' . $config_file;
  }

  // in the user home directory
  if (!is_null(drush_server_home())) {
    $configs['user'] = drush_server_home() . '/.' . $config_file;
  }

  // in $HOME/.drush directory
  if (!is_null(drush_server_home())) {
    $configs['home.drush'] = drush_server_home() . '/.drush/' . $config_file;
  }

  // In the system wide configuration folder.
  $configs['system'] = drush_get_context('ETC_PREFIX', '') . '/etc/drush/' . $config_file;

  // in the drush installation folder
  $configs['drush'] = dirname(__FILE__) . '/../' . $config_file;

  return empty($configs[$context]) ? '' : $configs[$context];
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
    $items[$command] = array(
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
 * Implementation of drush_hook_COMMAND().
 */
function drush_deploy_callback($command, $alias) {
  $alias_settings = drush_sitealias_get_record($alias);
  $command_parts = explode('-', $command);
  $method = isset($command_parts[1]) ? $method = $command_parts[1] : $method = 'deploy';

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

