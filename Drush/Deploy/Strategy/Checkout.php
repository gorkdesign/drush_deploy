<?php
namespace Drush\Deploy\Strategy;

/**
 * Implements the deployment strategy which does an SCM checkout on each
 * target host. This is the default deployment strategy for Capistrano.
 */
class Checkout extends Remote {
  /**
   * Returns the SCM's checkout command for the revision to deploy.
   *
   * @return string
   */
  protected function command() {
    return $this->git->checkout($this->revision(), $this->config->release_path);
  }
}

