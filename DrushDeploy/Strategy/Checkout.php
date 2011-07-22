<?php
namespace DrushDeploy\Strategy;

/**
 * Implements the deployment strategy which does an SCM checkout on each
 * target host. This is the default deployment strategy for Capistrano.
 */
class Checkout extends Remote {
  /**
   * Returns the SCM's checkout command for the revision to deploy.
   */
  protected function command() {
    return $this->git->checkout($this->configuration->revision, $this->configuration->release_path);
  }
}

