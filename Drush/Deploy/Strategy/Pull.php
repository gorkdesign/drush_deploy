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
    $command = $this->git->pull($this->config->revision, $this->config->root);

    try {
      $this->config->run($command);
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

}

