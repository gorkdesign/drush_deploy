# DRUSH DEPLOY

## About

Drush deploy is a deployment framework built on Drush. It is **heavily** influenced by [capistrano](https://github.com/capistrano/capistrano).

This project aims to help you deploy your **code** in a way that will cause minimal/no downtime, and let you easily rollback if problems occur.

This project will **not** help you deploy content. For that, check out the [Deploy](http://drupal.org/project/deploy) module.

## USAGE

You define the servers you want to deploy to using drush site alias groups. For example, you could put the folowing in ~/.drush/blah.aliases.drushrc.php:

        <?php
        $aliases['web1'] = array(
        'root' => '/var/www/drupal',
        'remote-user' => 'ubuntu',
        'remote-host' => 'web1.blah.com',
        );
        $aliases['web2'] = $aliases['web1'];
        $aliases['web2']['remote-host'] = 'web2.blah.com';
        ?>

Deploy stores it's settings in a deploy.drushrc.php file, which will go in the same locations as your drushrc.php and aliases.drushrc.php. Here is the minimal configuration needed to get started:

        <?php
        $options['application'] = 'blah';
        $options['deploy-repository'] = 'git@github.com:blah/site_repo.git';
        $options['branch'] = "master";
        $options['keep-releases'] = 3;
        $options['deploy-via'] = 'RemoteCache';
        $options['docroot'] = '/var/www/drupal';
        ?>

To then deploy the latest from the master brance of git@github.com:blah/site_repo.git to the web servers web1.blah.com and web2.blah.com, you would do:

    drush deploy @blah


## REQUIREMENTS

* php 5.3
* non-windows OS
