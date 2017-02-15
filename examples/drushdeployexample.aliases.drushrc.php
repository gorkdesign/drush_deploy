<?php
/*
 * This is an example Drush Site Alias file that has extra configuration
 * added for use with the Drush Deploy extension.
 */

/**
 * Global Parent Alias
 *
 * Aliases below that have a:
 * 'parent' => '@drushdeployexample.global'
 * will inherit the configuration from this section. If the same configuration
 * is defined in the child alias, it will take precedence. 
 * We use this section to define settings that are common to all Drush site aliases.
 * There is nothing special about the alias name 'global' this could be 'base' or
 * anything you like it to be.
 * Note: you can not reference the parent with only the alias name you must
 * use the full aliases name which includes the name of the aliases file.
 * For example for this alias file and the global alias, add the following to
 * another alias to have it inherit the settings from the global alias:
 * 'parent' => '@drushdeployexample.global',
 */
$aliases['global'] = array( // Parent alias not use for a site. Used to store common configuration for sites. Add 'parent' => '@drushdeployexample.global', to another alias to have these settings inherited.
  'application' => 'drupal', // This will always be drupal.
  'deploy-repository' => 'git@bitbucket.org:username/repositoryname.git', // git repository that you will be deploying you website from. Don't forget to setup keys if the repository is private.
  'deploy-via' => 'RemoteCache', // Drush deploy deployment method.
  'keep-releases' => '3', // When we run 'drush @sitealias deploy-cleanup' how many past deployments do we want to leave on the server?
  'drupal-major-version' => '8', // This option sets the Drupal major version. This is used in the deploy.drushrc.php file to determine whether to run the Drupal 7 or Drupal 8 tasks.
);

$aliases['dev'] = array(  // Development website - Drush site alias will be the name of this drush site aliases file followed by the alias. ie. @drushdeployexample.dev
  'root' => '/var/www', // Path to Drupal webroot
  'remote-user' => 'root', // Linux username on remote host
  'remote-host' => 'localhost', // Remote hostname
  'ssh-options' => '-p 9022', // Or any other port you specify when running the docker container
  'path-aliases' => array(
    '%files' => 'sites/default/files',  // Path to site files.
    ),
);
$aliases['test'] = array(  // Test website Drush site alias will be the name of this drush site aliases file followed by the alias. ie. @drushdeployexample.test
  'parent' => '@drushdeployexample.global',  // Inherit the settings from the given Drush site aliases. This one is referencing one that is above in this file.
  'root' => '/home/clients/websites/w_username/public_html/environmentname', // Path to Drupal webroot
  'uri' => 'environmentname.dev6.webenabled.net', // URL of the website where it can be reached in a web browser.
  'remote-user' => 'w_username', // Linux username on remote host
  'remote-host' => 'dev6.webenabled.net', // Remote hostname
//  'remote-environment' => 'webenabled',  // Custom setting for running custom tasks. Not required.
  'ssh-options' => '-p 22',    // port number ie. -p 22
  'branch' => 'test', // git branch from the deployment repository that this site should pull from.
  'deploy-to' => '/home/clients/websites/w_username/public_html/deploy', // Path to the directory where Drush deploy will deploy to.
  'path-aliases' => array(
    '%files' => 'sites/default/files',
    ),
);
$aliases['live'] = array(  // Live website - Drush site alias will be the name of this drush site aliases file followed by the alias. ie. @drushdeployexample.live
  'parent' => '@drushdeployexample.global', // Inherit the settings from the given Drush site aliases. This one is referencing one that is above in this file.
  'root' => '/home/username/example.com/public_html', // Path to Drupal webroot
  'uri' => 'example.com', // URL of the website where it can be reached in a web browser.
  'remote-user' => 'username', // Linux username on remote host
  'remote-host' => 'server123.example.net', // Remote hostname
//  'remote-environment' => 'examplehosting',  // Custom setting for running custom tasks. Not required.
  'ssh-options' => '-p 22',    // port number ie. -p 22
  'branch' => 'live', // git branch from the deployment repository that this site should pull from.
  'deploy-to' => '/home/username/example.com/deploy', // Path to the directory where Drush deploy will deploy to.
  'path-aliases' => array(
    '%files' => 'sites/default/files',
    ),
);
// MAGIC STARTS HERE.
//
// Remove "remote-host" from entries that correspond with the current server.
// This allows us to use the same alias file in all environments.
$ip = gethostbyname(php_uname('n'));
foreach ($aliases as &$alias) {
  if (empty($alias['remote-host'])) {
    continue;
  }
  if (gethostbyname($alias['remote-host']) == $ip) {
    unset($alias['remote-host']);
  }
}
