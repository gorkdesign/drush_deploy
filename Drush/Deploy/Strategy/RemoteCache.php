<?php
namespace Drush\Deploy\Strategy;
/**
 * Implements the deployment strategy that keeps a cached checkout of
 * the source code on each remote server. Each deploy simply updates the
 * cached checkout, and then does a copy from the cached copy to the
 * final deployment location.
 */
class RemoteCache extends Remote {
  /**
   * Executes the SCM command for this strategy and writes the REVISION
   * mark file to each host.
   */
  function deploy() {
    $this->updateRepositoryCache();
    $this->copyRepositoryCache();
  }

  function copyExclude() {
    return drush_get_option("copy_exclude", array());
  }

  function check() {
    $d = parent::check();
    $exclude = $this->copyExclude();
    if (!empty($exclude)) {
      $d->remote->command("rsync");
    }
    $d->remote()->writable($this->config->shared_path);
  }

  private function repositoryCache() {
    return $this->config->shared_path . '/' . drush_get_option("repository_cache", "cached-copy");
  }

  private function updateRepositoryCache() {
    $repo_cache = $this->repositoryCache();
    drush_log("updating the cached checkout on all servers");
    $command = 'if [ -d ' . $repo_cache . ' ]; then ';
    $command .= $this->git->sync($this->config->revision, $repo_cache) . ';';
    $command .= 'else ' . $this->git->checkout($this->config->revision, $repo_cache) . ';fi';
    try {
      $this->config->run($command);
    }
    //catch (CommandException $e) {
    catch (\Drush\Deploy\CommandException $e) {
      drush_set_error($e->getMessage());
    }
  }

  private function copyRepositoryCache() {
    drush_log(dt("copying the cached version to %s", $this->config->release_path));
    $exclude = $this->copyExclude();
      if (empty($exclude)) {
        try {
          $this->config->run("cp -RPp %s %s && %s", $this->repositoryCache(), $this->config->release_path, $this->mark());
        }
        catch (CommandException $e) {
          drush_set_error($e->getMessage());
        }
    }
    else {
      $exclusions = array_map(function($e) {
        return "--exclude=\"$e\"";
      }, $this->copyExclude());
      $exclusions = implode(" ", $exclusions);
      try {
        $this->config->run("rsync -lrpt %s %s/* %s && %s", $exclusions, $this->repositoryCache(), $this->config->release_path, $this->mark());
      }
      catch (CommandException $e) {
        drush_set_error($e->getMessage());
      }
    }
  }
}

