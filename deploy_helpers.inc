<?php

function deploy_task() {
  $args = func_get_args();
  $name = array_shift($args);
  if ($args[count($args) - 1] instanceof Closure) {
    $work = array_pop($args);
  }
  else {
    $work = null;
  }
  DrushDeploy\Deploy::$tasks[$name] = $work;
}

function deploy_before($before_task, $task) {
  DrushDeploy\Deploy::$before[$before_task][] = $task;
}

function deploy_after($after_task, $task) {
  DrushDeploy\Deploy::$after[$after_task][] = $task;
}
