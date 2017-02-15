<?php
/*
 * This is an example Drush Site Alias file that has extra configuration
 * added for a custom task on a remote-environment (hosting, server, etc.).
 * for this example we will implement using the sync_via_http.
 * See the sync_via_http.drush.inc file that is included for more information about it.
 * The **test** alias below has an additional option set that we have created for this.
 * You could call yours what ever you want just remember to use that name 
 * in your deploy.drushrc.php file task. We are using the option:
 * remote-environment
 * Read through the comments in the 
 * $aliases['test'] = array(
 * section and see the:
 * function deploy_db_http_symlinks_task()
 * and the:
 * function deploy_db_http_symlinks_task_8()
 * in the
 * deploy.drushrc.php
 * file.
 * In the functions above you will notice that we are only performing this
 * task for sites where remote-environment=webenabled
 * The task will add a symlink to the database dump so that it can be 
 * retrieved via HTTP.
 */

/**
 * Global Parent Alias
 *
 */
$aliases['global'] = array(
  'application' => 'drupal',
  'deploy-repository' => 'git@bitbucket.org:username/repositoryname.git',
  'deploy-via' => 'RemoteCache',
  'keep-releases' => '3',
  'drupal-major-version' => '8',
);

$aliases['dev'] = array(
  'root' => '/var/www',
  'remote-user' => 'root',
  'remote-host' => 'localhost',
  'ssh-options' => '-p 9022', // Or any other port you specify when running the docker container
  'path-aliases' => array(
    '%files' => 'sites/default/files', 
    ),
);
$aliases['test'] = array(
  'parent' => '@customtasksexample.global',
  'root' => '/home/clients/websites/w_username/public_html/environmentname',
  'uri' => 'environmentname.dev6.webenabled.net',
  'remote-user' => 'w_username',
  'remote-host' => 'dev6.webenabled.net',
  'remote-environment' => 'webenabled',  // This is an option we have added to run custom tasks. The name of the option 'remote-environment' and the value can be adjusted to your liking.
  'ssh-options' => '-p 22',    // port number ie. -p 22
  'branch' => 'test',
  'deploy-to' => '/home/clients/websites/w_username/public_html/deploy',
  'path-aliases' => array(
    '%files' => 'sites/default/files',
    ),
 'source-command-specific' => array(  // This source-command-specific array sets the URL, username and password that will allow a remote developer to retrieve the database dump via HTTP.
  'sql-sync'  => array(
    'http-sync'  => 'http://username.dev6.webenabled.net/dbouthouse/my-database-dump.sql',
    'http-sync-user' => 'dbdude',
    'http-sync-password' => 'supersecurepassword',
    ),
  ),
  'target-command-specific' => array(
    'sql-sync' => array(
//      'sanitize' => TRUE, // Use this if you want sensitive information sanitize in the database dump.
//      'confirm-sanitizations' => TRUE, // Use this if you want to confirm whether the sanitation should be preformed each time.
      'no-ordered-dump' => TRUE,
      'no-cache' => TRUE,
    ),
  ),
  'command-specific' => array( // This command-specific array sets the file the sql-dump should be placed in.
    'sql-dump' => array(
      'result-file' => '/home/clients/websites/w_username/public_html/deploy/shared/dbouthouse/my-database-dump.sql',
      'ordered-dump' => FALSE,
  ),
  ),
);
$aliases['live'] = array(
  'parent' => '@customtasksexample.global',
  'root' => '/home/username/example.com/public_html',
  'uri' => 'example.com',
  'remote-user' => 'username',
  'remote-host' => 'server123.example.net',
  'remote-environment' => 'examplehosting',
  'ssh-options' => '-p 22',    // port number ie. -p 22
  'branch' => 'live',
  'deploy-to' => '/home/username/example.com/deploy',
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
