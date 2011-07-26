<?php
/**
 * @file
 *
 * @author Mark Sonnabaum (http://drupal.org/user/75278)
 *
 */

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
  $class = '\DrushDeploy\Deploy';
  $methods = get_class_methods($class);
  if (!empty($methods)) {
    foreach($methods as $method) {
      if (!isset($items['deploy-' . $method])) {
        $items['deploy-' . $method] = array(
          'description' => '',
          'arguments' => array(),
          'options' => array(),
          'bootstrap' => DRUSH_BOOTSTRAP_DRUSH, // No bootstrap at all.
        );
      }
    }
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

  $blah = $deploy->{$method}();
  drush_print($blah);
}

function drush_deploy_transaction(&$obj = NULL, $commands) {
  if ($obj) {
    foreach ($commands as $i => $cmd) {
      try {
        $obj->{$cmd}();
      }
      catch (CommandException $e) {
        foreach ($commands as $ri => $rcmd) {
          if (is_callable(array($obj, $rcmd . '_rollback'))) {
            drush_print('ROLLBACK: ' . $rcmd);
            $obj->{$rcmd . '_rollback'}();
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

