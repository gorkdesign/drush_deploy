# Drush Deploy - Examples

This folder contains example files. Some of which are necessary to successfully do a deployment with Drush deploy.

- **[deploy.drushrc.php](deploy.drushrc.php)** - This is your configuration file for Drush deploy. It is where deployment tasks are defined.
- **Example Drush Site Aliases Files** - Drush Deploy uses Drush site Aliases and will not work without them. These contain site specific configuration for deployment.
    - **[drushdeployexample.drushrc.php](drushdeployexample.drushrc.php)** - Example Drush Site Aliases file with example values. It is fully commented.
    - **[drushdeploytemplate.drushrc.php](drushdeploytemplate.drushrc.php)** - Template Drush Site Aliases file. Make a copy this file. Rename and edit for your configuration. Most values are empty. One search and replace will let you set values related to the name of the Drush Site aliases file.
    - **[customtasksexample.aliases.drushrc.php](customtasksexample.aliases.drushrc.php)** - This Drush Site Aliase file serves as and example of added configuration options to help with custom Drush deploy tasks. This file shows needed configuration to setup the sync_via_http functionality. sync_via_http allows developers to download a copy of a database dump
- **[policy.drushrc.php](policy.drushrc.php)** - This files is included with a function for the Drush site aliases **parent** functionality that is useful  but is planned to be removed from Drush. The **parent** functionality is use in the example Drush Site Aliases. Without this function in **policy.drushrc.php** this functionality may stop working in the future.
- **[sync_via_http.drush.inc](sync_via_http.drush.inc)** - this file is included as it is used in the example of how to run custom tasks for hosts or sites.
- **[README-sync_via_http.md](examples/README-sync_via_http.md)** - Documentation for sync_via_http.drush.inc file.
