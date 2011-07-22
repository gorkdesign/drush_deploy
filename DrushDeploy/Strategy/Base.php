<?php
namespace DrushDeploy\Strategy;
abstract class Base {
  # This class defines the abstract interface for all Capistrano
  # deployment strategies. Subclasses must implement at least the
  # #deploy! method.
  public $configuration;

  # Instantiates a strategy with a reference to the given configuration.
  //function initialize($config = array()) {
  function __construct($config) {
    $this->configuration = $config;
    $this->git = $config->git;
  }

  # Executes the necessary commands to deploy the revision of the source
  # code identified by the +revision+ variable. Additionally, this
  # should write the value of the +revision+ variable to a file called
  # REVISION, in the base of the deployed revision. This file is used by
  # other tasks, to perform diffs and such.
  function deploy() {
    throw NotImplementedException("`deploy!' is not implemented by #{self.class.name}");
  }

  # Performs a check on the remote hosts to determine whether everything
  # is setup such that a deploy could succeed.
  function check() {
    /*
    Dependencies.new(configuration) do |d|
      d.remote.directory(configuration[:releases_path]).or("`#{configuration[:releases_path]}' does not exist. Please run `cap deploy:setup'.")
      d.remote.writable(configuration[:deploy_to]).or("You do not have permissions to write to `#{configuration[:deploy_to]}'.")
      d.remote.writable(configuration[:releases_path]).or("You do not have permissions to write to `#{configuration[:releases_path]}'.")
     */
  }

/*
  # This is to allow helper methods like "run" and "put" to be more
  # easily accessible to strategy implementations.
   protected function method_missing($sym, *args, &block) {
    if (configuration.respond_to?(sym)) {
      configuration.send(sym, *args, &block);
    }
    else {
      super;
    }
  }
 */
  # A wrapper for Kernel#system that logs the command being executed.
  /*
  protected function system(*args) {
    cmd = args.join(' ')
      result = nil
      if (RUBY_PLATFORM =~ /win32/) {
        cmd = cmd.split(/\s+/).collect {|w| w.match(/^[\w+]+:\/\//) ? w : w.gsub('/', '\\') }.join(' ') # Split command by spaces, change / by \\ unless element is a some+thing:// 
          cmd.gsub!(/^cd /,'cd /D ') # Replace cd with cd /D
          cmd.gsub!(/&& cd /,'&& cd /D ') # Replace cd with cd /D
          logger.trace "executing locally: #{cmd}"
          elapsed = Benchmark.realtime do
          result = super(cmd)
        }
      }
      else {
        logger.trace "executing locally: #{cmd}"
          elapsed = Benchmark.realtime do
          result = super
      }

    logger.trace "command finished in #{(elapsed * 1000).round}ms"
      result
  }
   */
  # The revision to deploy. Must return a real revision identifier,
  # and not a pseudo-id.
  private function revision() {
    $configuration['real_revision'];
  }
}
