<?php

namespace DrushDeploy;

class Git {
  // Sets the default command name for this SCM on your *local* machine.
  // Users may override this by setting the scm_command variable.
  public $default_command = "git";
  public $config;

  function __construct($config) {
    $this->config = $config;
  }

  // When referencing "head", use the branch we want to deploy or, by
  // default, Git's reference of HEAD (the latest changeset in the default
  // branch, usually called "master").
  function head() {
    return drush_get_option('branch', 'HEAD');
  }

  function origin() {
    return drush_get_option('remote', 'origin');
  }


  // Performs a clone on the remote machine, then checkout on the branch
  // you want to deploy.
  function checkout($revision, $destination) {
    $remote = $this->origin();

    $args = array();
    if ($remote != 'origin') $args[] = "-o #{remote}";

    if ($depth = drush_get_option('git_shallow_clone', FALSE)) {
      $args[] = "--depth $depth";
    }

    $execute = array();
    $args_str = implode(' ', $args);
    $repo = $this->config->repository;
    $execute[] = "git clone $verbose $args_str $repo $destination";
    // checkout into a local branch rather than a detached HEAD
    $execute[] = "cd $destination && git checkout $verbose -b deploy $revision";

    if (drush_get_option('git_enable_submodules', FALSE)) {
      $execute[] = "git submodule $verbose init";
      $execute[] = "git submodule $verbose sync";
      $execute[] = "git submodule $verbose update --init --recursive";
    }

    $cmd = implode(' && ', $execute);
    return $cmd;
  }

  /**
   * An expensive export. Performs a checkout as above, then
   * removes the repo.
   *
   * @param $revision
   * @param $destination
   */
  function export($revision, $destination) {
    $this->checkout($revision, $destination) . " && rm -Rf " . $destination . "/.git";
  }

  /**
   * Merges the changes to 'head' since the last fetch, for remote_cache
   * deployment strategy
   *
   * @param $revision
   * @param $destination
   * @return string
   */
  function sync($revision, $destination) {
    $remote  = drush_get_option('remote', 'origin');

    $execute = array("cd $destination");

    // Use git-config to setup a remote tracking branches. Could use
    // git-remote but it complains when a remote of the same name already
    // exists, git-config will just silenty overwrite the setting every
    // time. This could cause wierd-ness in the remote cache if the url
    // changes between calls, but as long as the repositories are all
    // based from each other it should still work fine.
    if ($remote != 'origin') {
      $execute[] = "git config remote.$remote.url $this->repository";
      $execute[] = "git config remote.$remote.fetch +refs/heads/*:refs/remotes/$remote/*";
    }

    // since we're in a local branch already, just reset to specified revision rather than merge
    $execute[] = "git fetch $verbose $remote && git fetch --tags $verbose $remote && git reset $verbose --hard $revision";

    if (drush_get_option('git_enable_submodules', FALSE)) {
      $execute[] = "git submodule $verbose init";
      $execute[] = "for mod in `git submodule status | awk '{ print $2 }'`; do git config -f .git/config submodule.\${mod}.url `git config -f .gitmodules --get submodule.\${mod}.url` && echo Synced \$mod; done";
      $execute[] = "git submodule $verbose sync";
      $execute[] = "git submodule $verbose update --init --recursive";
    }

    // Make sure there's nothing else lying around in the repository (for
    // example, a submodule that has subsequently been removed).
    $execute[] = "git clean $verbose -d -x -f";

    $cmd = implode(' && ', $execute);
    return $cmd;
  }


