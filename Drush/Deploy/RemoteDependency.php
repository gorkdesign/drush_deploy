<?php
/**
 */
namespace Drush\Deploy;
class RemoteDependency {
  public $config;
  public $hosts;
  public $success = TRUE;

  function __construct($config) {
    $this->config = $config;
    return $this;
  }

  function directory($path, $options = array()) {
    $message = isset($options['message']) ? $options['message'] : "`%s' is not a directory";
    $this->attempt("test -d $path", $options);
    if (!$this->success) drush_set_error(sprintf($message, $path));
    return $this;
  }

  function file($path, $options = array()) {
    $message = isset($options['message']) ? $options['message'] : "`%s' is not a file";
    $this->attempt("test -f $path", $options);
    if (!$this->success) drush_set_error(sprintf($message, $path));
    return $this;
  }

  function writable($path, $options = array()) {
    $message = isset($options['message']) ? $options['message'] : "`%s' is not writable";
    $this->attempt("test -w $path", $options);
    if (!$this->success) drush_set_error(sprintf($message, $path));
    return $this;
  }

  function command($command, $options = array()) {
    $message = isset($options['message']) ? $options['message'] : "`%s' could not be found in the path";
    $this->attempt("which $command", $options);
    if (!$this->success) drush_set_error(sprintf($message, $command));
    return $this;
  }

  private function attempt($command, $options) {
    if (!$this->success) return;
    try {
      $this->config->run($command);
    }
    catch (CommandException $e) {
      $this->success = FALSE;
      drush_set_error($e->getMessage());
    }
    return $this;
  }
}
