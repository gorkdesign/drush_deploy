<?php
namespace Drush\Deploy;
class Dependencies {
  public $config;
  //private $dependencies = array();

  function __construct($configuration) {//, $dependencies = array()) {
    $this->config = $configuration;
    //$this->dependencies = $dependencies;
  }

  function remote() {
    $dep = new RemoteDependency($this->config);
    //$this->dependencies[] = $dep;
    return $dep;
  }

  function local() {
    $dep = new LocalDependency($this->config);
    //$this->dependencies[] = $dep;
    return $dep;
  }
}
