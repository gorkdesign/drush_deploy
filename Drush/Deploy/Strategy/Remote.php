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
    throw NotImplementedException("`deploy' is not implemented by " . __CLASS__);
    $commands[] = $this->command();
    $commands[] = $this->mark();
    $commands = array_filter($commands);
    $command = implode(' && ', $commands);
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

}

