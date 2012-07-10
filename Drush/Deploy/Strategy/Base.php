<?php
/**
 * This class defines the abstract interface for all deployment
 * strategies. Subclasses must implement at least the deploy method.
 */
namespace Drush\Deploy\Strategy;
abstract class Base {
  public $config;

  /**
   * Instantiates a strategy with a reference to the given configuration.
   *
   * @param $config
   */
  function __construct($config) {
    $this->config = $config;
    $this->git = $config->git;
  }

  /**
   * Executes the necessary commands to deploy the revision of the source
   * code identified by the +revision+ variable. Additionally, this
   * should write the value of the +revision+ variable to a file called
   * REVISION, in the base of the deployed revision. This file is used by
   * other tasks, to perform diffs and such.
   *
   * @throws
   * @return void
   */
  function deploy() {
    throw NotImplementedException("`deploy' is not implemented by " . __CLASS__);
  }

  /**
   * Performs a check on the remote hosts to determine whether everything
   * is setup such that a deploy could succeed.
   *
   * @return \Drush\Deploy\Dependencies
   */
  function check() {
    $deps = new \Drush\Deploy\Dependencies($this->config);
    $deps->remote()->directory($this->config->releases_path, array('message' => "`%s' does not exist. Please run `drush deploy-setup'."));
    $deps->remote()->writable($this->config->deploy_to, array('message' => "You do not have permissions to write to `%s'."));
    $deps->remote()->writable($this->config->releases_path, array('message' => "You do not have permissions to write to `%s'."));
    return $deps;
  }

  /**
   * The revision to deploy. Must return a real revision identifier,
   * and not a pseudo-id.
   */
  protected function revision() {
    return $this->config->real_revision;
  }
}
