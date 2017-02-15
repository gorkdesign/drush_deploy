# Drush Deploy

## About

Drush deploy is a deployment framework built on Drush. It is **heavily** influenced by [capistrano](https://github.com/capistrano/capistrano).

This project aims to help you deploy your **code** in a way that will cause minimal/no downtime, and let you easily rollback if problems occur.

This project will **not** help you deploy content. For that, check out the [Deploy](http://drupal.org/project/deploy) module.

## USAGE

* You define the servers you want to deploy to using drush site alias groups.
For example, you could put the following in ~/.drush/foo.aliases.drushrc.php:

        <?php
        $aliases['web1'] = array(
           'root' => '/var/www/deploy',
           'remote-host' => 'web1.foo.com',
           'remote-user' => 'ubuntu',
           'command-specific' => array(
             'deploy-deploy' => array(
               'branch' => 'dev',
              ),
	        'deploy-setup' => array(
                  'branch' => 'dev',
               ),
           ),
        );

        $aliases['web2'] = $aliases['web1'];
        $aliases['web2']['remote-host'] = 'web2.foo.com';
        ?>

The command specific array allows to override options defined on the step above. It can
be used to deploy on different environments.

* All this will suppose your Drupal install is in a folder named **deploy** at the root of the
git repository. Update paths if needed.

* Deploy stores its settings in a **deploy.drushrc.php** file, which will go in the same
locations as your **drushrc.php** and ** *__aliasesname__*.drushrc.php**. Here is the minimal configuration
needed to get started:

		<?php
        $options['application'] = 'foo';
        $options['deploy-repository'] = 'git@github.com:foo/site_repo.git';
        $options['branch'] = "master";
        $options['keep-releases'] = 3;
        $options['deploy-via'] = 'RemoteCache';
		$options['docroot'] = '/var/www/deploy';
        $options['deploy-to'] = '/var/www/deploy';
        ?>

This can be placed in **deploy.drushrc.php**. However then this applies to all sites that you run **drush deploy** commands on. It is also possible to place these setting or the ones you want override in your Drush Site Alias file. See the included [examples/drushdeployexample.aliases.drushrc.php](examples/drushdeployexample.aliases.drushrc.php) file.

* Then run

    `drush deploy-setup @foo.web1`

This will create a basic folder structure in **/var/www/deploy/**

* You need to put all untracked files in the **/var/www/deploy/shared** folder. In a classic
Drupal install, your default folder goes there. Attribute ownership of the default
folder to **www-data** (the apache webserver user).

## CONFIGURATION OPTIONS

Additional configuration options for your recipe can be included in your
**deploy.drushrc.php** file as follows:


        <?php
        // Initialize, sync, and update submodules with 'git submodule' commands
        $options['git_enable_submodules'] = TRUE;
        // Run additional tasks after the 'current' symlink has been updated
        $options['after']['deploy-symlink'][] = 'my_custom_task';
        // Build a drush make file.
        $options['before']['deploy-symlink'][] = 'my_custom_make_build_task';

        /**
         * The task needs to be defined with a @task "decorator" in the comment block preceding it
         * @task
         */
        function my_custom_task($d) {
          $d->run("ln -s /var/www/mysite.com/deploy/shared/settings.php %s/sites/default/settings.php", $d->latest_release());
          $d->run("ln -s /var/www/mysite.com/deploy/shared/files %s/sites/default/files", $d->latest_release());
        }

        /**
         * Build a drush make file that is located at 'path-to-makefile'.
         * @task
         */
         function my_custom_make_build_task($d) {
           $d->run('cd %s/path-to-makefile && drush make -y foo.make docroot', $d->release_path);
         }
        ?>

* Any task you need will need to be defined. Tasks are to be put in **deploy.drushrc.php**.
Minimal tasks are :

		<?php

		/**
         * The task needs to be defined with a @task "decorator" in the comment block preceding it
		 * @task
		 */
		function link_default($d) {
		    $d->run('cd %s && rm -rf drupal/sites/default', $d->latest_release());
		    $d->run('ln -s %s/shared/default %s/drupal/sites/', $d->deploy_to(), $d->latest_release());
		}
		$options['before']['deploy-symlink'][] = 'link_default';

		/**
		 * @task
		 */
		function chown_drupal($d) {
		    $d->run('chown -R www-data:www-data %s/drupal', $d->latest_release());
		}
		$options['before']['deploy-symlink'][] = 'chown_drupal';

		/**
		 * @task
		 * Underscored to avoid namespace conflict.
		 */
		function _updatedb($d) {
		    $d->run('cd %s/drupal && drush updatedb', $d->latest_release());
		    $d->run('cd %s/drupal && drush cc all', $d->latest_release());
		}
		$options['before']['deploy-symlink'][] = '_updatedb';

		?>
### sync_via_http custom command example
The **sync_via_http.drush.inc** file also included with Drush as an example, allows syncing the database via HTTP. Setting up the symlink to the database dump is shown as an example of a custom drush deploy task based on custom options set in the drush site aliases file. See the comments in [examples/customtasksexample.aliases.drushrc.php](examples/customtasksexample.aliases.drushrc.php) and the comments in [examples/deploy.drushrc.php](examples/deploy.drushrc.php) on the  **deploy_db_http_symlinks_task()** and **deploy_db_http_symlinks_task_8()** functions.

## AVAILABLE COMMANDs

For available commands, check **Deploy.php** file.

* `deploy`
    Updates your remote cache.
    Initializes and updates git submodules.
    Creates a new release directory.
    Copies your current codebase to the release directory.
    Links the ‚current‘ directory with your new deployed code.
    Executes your tasks.

* `deploy-rollback`
    Relinks the current directory with the last release.
    Removes the faulty release.

* `deploy-setup`


### Setup

First setup the web servers with running:

    `drush deploy-setup @live`

### Deployment

* To then deploy the latest from the **master** branch of  `git@github.com:foo/site_repo.git`
to both of the web servers ** web1.foo.com ** and ** web2.foo.com ** , you would do:

    `drush deploy @foo`


## REQUIREMENTS

* php 5.3 and above
* non-windows OS

## Examples
See the [examples](examples) folder for example files and the [README.md](examples/README.md) therein for more information about them.
