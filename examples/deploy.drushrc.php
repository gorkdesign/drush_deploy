<?php

// Run these tasks before the 'current' symlink has been updated
    $options['before']['deploy-symlink'][] = 'deploy_before_deploy_symlink_tasks';
// Run these tasks after the 'current' symlink has been updated
    $options['after']['deploy-symlink'][] = 'deploy_after_deploy_symlink_tasks';
// Check to see if this is a Drupa 7 or 8 site so we can run different tasks for each.
$drupal_major_version = NULL;
if (!function_exists("drupal_major_version_check")) {
  function drupal_major_version_check($d) {
    // If the drupal-major-version option is not set in the site alias file, then assume it is Drupal 7 for backward compatibility.
    $GLOBALS["drupal_major_version"] = drush_get_option('drupal-major-version', $default = '7');
    global $drupal_major_version;
    if (NULL !== $drupal_major_version) {
      drush_log("Checked Drupal major version. It is: " . $drupal_major_version, $type = completed);
    }
  }
}
// Default Group of Tasks that should run before the new symlinks are created.
// This function should be used to overwrite in site specific aliases.drushrc.php files
if (!function_exists("deploy_before_deploy_symlink_tasks")) {
  function deploy_before_deploy_symlink_tasks($d) {
      drupal_major_version_check($d);
      global $drupal_major_version;
      switch ($drupal_major_version) {
        case 7: // Tasks to run for Drupal 7 sites.
          deploy_settings_local_php_task($d);
          deploy_symlinks_task($d);
          deploy_db_http_symlinks_task($d);
          break;
        case 8: // Tasks to run for Drupal 8 installed with Composer.
          deploy_settings_local_php_task_8($d);
          deploy_symlinks_task_8($d);
          deploy_db_http_symlinks_task_8($d);
          break;
        default:
          drush_log("Drush Deploy is not configured for that version of Drupal, exiting", $type = error);
          exit(1);
      }
  }
}
// Default Group of Tasks that should run after the new symlinks are created.
// This function should be used to overwrite in site specific aliases.drushrc.php files
if (!function_exists("deploy_after_deploy_symlink_tasks")) {
  function deploy_after_deploy_symlink_tasks($d) {
    global $drupal_major_version;
      switch ($drupal_major_version) {
        case 7: // Tasks to run for Drupal 7 sites.
          deploy_update_task($d);
          break;
        case 8: // Tasks to run for Drupal 8 installed with Composer.
          deploy_composer_install_task_8($d);
          deploy_configuration_import_task_8($d);
          deploy_update_db_task_8($d);
          deploy_configuration_import_task_8($d); // We run this a second time because if modules were installed the configuration for them could not be imported the first time, but they were installed.
          deploy_cache_task_d8($d);
          break;
        default:
          drush_log("Drush Deploy is not configured for that version of Drupal, exiting", $type = error);
          exit(1);
      }
  }
}

//////// TASK FUNCTIONS /////////////
// Drupal 8 task functions end in _8

//////// Drupal 7 TASK FUNCTIONS /////////////
/**
 * Copy the settings.local.php from one directory up from the webroot.
 * A task needs to be defined with a @task "decorator" in the comment block preceding it
 * @task
 * @mandatory
 */
if (!function_exists("deploy_settings_local_php_task")) {
  function deploy_settings_local_php_task($d) {
    $d->run("cp ". ($oneupfromwebroot = dirname(drush_get_option('root'))) . "/settings.local.php %s/sites/default/settings.local.php 2>/dev/null || :", $d->latest_release());
//    drush_log("Deployed settings.local.php file.", $type = 'notice');
  }
}

/**
 * Create a link to Drupal's shared files (sites/default/files).
 * A task needs to be defined with a @task "decorator" in the comment block preceding it
 * @task
 */
if (!function_exists("deploy_symlinks_task")) {
  function deploy_symlinks_task($d) {
         $d->run("ln -s " . ($oneupfromwebroot = dirname(drush_get_option('root'))) . "/deploy/shared/files %s/sites/default/files", $d->latest_release());
//         drush_log("Deployed symlinks.", $type = notice);
      }
}

/**
 * Create a link to the database dump for download via http for sites
 * where remote-environment=webenabled.
 * A task needs to be defined with a @task "decorator" in the comment block preceding it
 * @task
 */
if (!function_exists("deploy_db_http_symlinks_task")) {
  function deploy_db_http_symlinks_task($d) {
    if (strpos($d->sites[0]['remote-environment'], 'webenabled') !== FALSE) {
      $d->run("ln -s " . ($oneupfromwebroot = dirname(drush_get_option('root'))) . "/deploy/shared/dbouthouse %s/dbouthouse", $d->latest_release());
//      drush_log("Created database HTTP symlink.", $type = notice);
        }

    }
}
/**
 * Move the the webroot and run database update.
 * A task needs to be defined with a @task "decorator" in the comment block preceding it
 * @task
 */
if (!function_exists("deploy_update_task")) {
  function deploy_update_task($d) {
    $d->run_once("cd %s && drush updb -y", $d->latest_release());
//    drush_log("Updated database, if required.", $type = notice);
  }
}


/**
 * Move to the webroot and clear all caches
 * A task needs to be defined with a @task "decorator" in the comment block preceding it
 * @task
 */
if (!function_exists("deploy_cache_task")) {
  function deploy_cache_task($d) {
    $d->run_once("cd %s && drush cc all -y", $d->latest_release());
//    drush_log("Cleared all caches.", $type = notice);
  }
}

/**
 * Move to the webroot and clear the CSS and Java script caches.
 * A task needs to be defined with a @task "decorator" in the comment block preceding it
 * @task
 */
if (!function_exists("deploy_cache_css_js_task")) {
  function deploy_cache_css_js_task($d) {
    $d->run_once("cd %s && drush cc css-js -y", $d->latest_release());
//    drush_log("Cleared css and js caches.", $type = notice);
  }
}

//////// Drupal 8 TASK FUNCTIONS /////////////
// Drupal 8 task functions end in _8

/**
 * Copy the settings.local.php from two directory up from the webroot (docroot).
 * A task needs to be defined with a @task "decorator" in the comment block preceding it
 * @task
 * @mandatory
 */
if (!function_exists("deploy_settings_local_php_task_8")) {
  function deploy_settings_local_php_task_8($d) {
    $d->run("cp ". ($oneupfromwebroot = dirname(drush_get_option('root'))) . "/settings.local.php %s/docroot/sites/default/settings.local.php 2>/dev/null || :", $d->latest_release());
//    drush_log("Deployed settings.local.php file.", $type = 'notice');
  }
}

/**
 * Create a link to Drupal's shared files (sites/default/files).
 * A task needs to be defined with a @task "decorator" in the comment block preceding it
 * @task
 */
if (!function_exists("deploy_symlinks_task_8")) {
  function deploy_symlinks_task_8($d) {
         $d->run("ln -s " . ($oneupfromwebroot = dirname(drush_get_option('root'))) . "/deploy/shared/files %s/docroot/sites/default/files", $d->latest_release());
//         drush_log("Deployed symlinks.", $type = notice);
      }
}

/**
 * Create a link to the database dump for download via http for sites
 * where remote-environment=webenabled.
 * A task needs to be defined with a @task "decorator" in the comment block preceding it
 *** NOTE: This D8 version of the function is no different than the D7 one.
 * @task
 */
if (!function_exists("deploy_db_http_symlinks_task_8")) {
  function deploy_db_http_symlinks_task_8($d) {
    if (strpos($d->sites[0]['remote-environment'], 'webenabled') !== FALSE) {
      $d->run("ln -s " . ($oneupfromwebroot = dirname(drush_get_option('root'))) . "/deploy/shared/dbouthouse %s/dbouthouse", $d->latest_release());
//      drush_log("Created database HTTP symlink.", $type = notice);
        }

    }
}

/**
 * Move to the Composer project root and run "composer install".
 * Tasks needs to be defined with a @task "decorator" in the comment block preceding it
 * @task
 */
if (!function_exists("deploy_composer_install_task_8")) {
  function deploy_composer_install_task_8($d) {
    $d->run_once("cd %s && composer install --no-progress --no-suggest", $d->latest_release());
//    drush_log("Updated database, if required.", $type = notice);
  }
}

/**
 * Move to the webroot and import the configuration into the database.
 * A task needs to be defined with a @task "decorator" in the comment block preceding it
 * @task
 */
if (!function_exists("deploy_configuration_import_task_8")) {
  function deploy_configuration_import_task_8($d) {
    $d->run_once("cd %s/docroot && drush cim -y", $d->latest_release());
//    drush_log("Updated database, if required.", $type = notice);
  }
}

/**
 * Move to the webroot and run database update.
 * A task needs to be defined with a @task "decorator" in the comment block preceding it
 * @task
 */
if (!function_exists("deploy_update_db_task_8")) {
  function deploy_update_db_task_8($d) {
    $d->run_once("cd %s/docroot && drush updb -y", $d->latest_release());
//    drush_log("Updated database, if required.", $type = notice);
  }
}


/**
 * A task needs to be defined with a @task "decorator" in the comment block preceding it
 * @task
 */
if (!function_exists("deploy_cache_task_d8")) {
  function deploy_cache_task_d8($d) {
    $d->run_once("cd %s/docroot && drush cr -y", $d->latest_release());
//    drush_log("Cleared all caches.", $type = notice);
  }
}
