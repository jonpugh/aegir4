<?php

namespace Aegir\Provision\Model;

class Environment {

  /**
   * @var string
   * Must be unique within a project.
   */
  public $name;

  /**
   * @var string
   * Project this environment belongs to.
   */
  public $project;

}
