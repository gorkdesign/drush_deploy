<?php
/**
 */

namespace Drush\Deploy;
class RemoteDependency {
  public $config;
  public $hosts;

  function __construct($config) {
    $this->config = $config;
    $this->success = TRUE;
    $this->hosts = NULL;
  }

  function directory($path, $options = array()) {
    $message = isset($options['message']) ?: "`%s' is not a directory";
    $this->attempt("test -d $path", $options);
    if (!$this->success) drush_set_error(sprintf($message, $path));
    return $this;
  }

  function file($path, $options = array()) {
    $message = isset($options['message']) ?: "`%s' is not a file";
    $this->attempt("test -f $path", $options);
    if (!$this->success) drush_set_error(sprintf($message, $path));
    return $this;
  }

  function writable($path, $options = array()) {
    $message = isset($options['message']) ?: "`%s' is not writable";
    $this->attempt("test -w $path", $options);
    if (!$this->success) drush_set_error(sprintf($message, $path));
    return $this;
  }

  function command($command, $options = array()) {
    $message = isset($options['message']) ?: "`%s' could not be found in the path";
    $this->attempt("which $command", $options);
    if (!$this->success) drush_set_error(sprintf($message, $command));
    return $this;
  }

  private function attempt($command, $options) {
    if (!$this->success) return;
    if (!drush_shell_exec($command)) {
      $this->success = FALSE;
      //drush_set_error($e);
    }
    return $this;
  }
}
