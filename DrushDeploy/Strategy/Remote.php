<?php
namespace DrushDeploy\Strategy;
/**
 * An abstract superclass, which forms the base for all deployment
 * strategies which work by grabbing the code from the repository directly
 * from remote host. This includes deploying by checkout (the default),
 * and deploying by export.
 */
class Remote extends Base {
  /**
   *  Executes the SCM command for this strategy and writes the REVISION
   * mark file to each host.
   */
  function deploy() {
    $command = $this->command() . ' && ' . $this->mark();
    $this->configuration->run($command);
  }

  function check() {
  }


  /**
   * Runs the given command, filtering output back through the
   * handle_data filter of the SCM implementation.
   */
  protected function scm_run($command) {
  }

  /**
   * An abstract method which must be overridden in subclasses, to
   * return the actual SCM command(s) which must be executed on each
   * target host in order to perform the deployment.
   */
  protected function command() {
  }

  /**
   * Returns the command which will write the identifier of the
   * revision being deployed to the REVISION file on each host.
   */
  protected function mark() {
    return "(echo " . $this->configuration->revision . " > " . $this->configuration->release_path . "/REVISION)";
  }
}

