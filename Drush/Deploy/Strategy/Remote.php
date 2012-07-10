<?php
namespace Drush\Deploy\Strategy;
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
    $this->config->run($command);
  }

  function check() {
    $d = parent::check();
    $d->remote()->command("git");
    return $d;
  }

  /**
   * An abstract method which must be overridden in subclasses, to
   * return the actual SCM command(s) which must be executed on each
   * target host in order to perform the deployment.
   */
  protected function command() {
    throw NotImplementedException("`command' is not implemented by " . __CLASS__);
  }


  /**
   * Returns the command which will write the identifier of the
   * revision being deployed to the REVISION file on each host.
   */
  protected function mark() {
    return "(echo " . $this->revision() . " > " . $this->config->release_path . "/REVISION)";
  }
}

