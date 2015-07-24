<?php
namespace Drush\Deploy\Strategy;

/**
 * Implements the deployment strategy which does an SCM pull on each
 * target host. 
 */
class Pull extends Remote {
  /**
   * Run a git pull
   *
   * @return string
   */
  function deploy() {

    $command_clone = 'cd ' . $this->config->root . ' && git rev-parse --is-inside-work-tree > /dev/null 2>&1; ';
    $command_clone .= 'if [ "$?" -ne 0 ]; then ';
    $command_clone .= $this->git->clone_only($this->config->revision, $this->config->root) . ';fi; ';

    $command_pull = $this->git->pull($this->config->revision, $this->config->root);

    try {
      $this->config->run($command_clone);
      $this->config->run($command_pull);
    }
    catch (CommandException $e) {
      drush_set_error($e->getMessage());
    }
  }

  /**
   * Pull Strategy does not need symlink deployment tasks
   */
  function getDeployTasks() {
    return array('update-code');
  }  

  /**
   * Pull Strategy doesn't have releases, so the "lastest release" is the root directory
   */
  function getLatestRelease(){
    return $this->config->root;
  }

}

