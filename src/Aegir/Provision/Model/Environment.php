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

  /**
   * @var string
   * Server the server this environment should live on.
   */
  public $server;

  /**
   * @var string
   * The system URL of the environment.
   */
  public $url;

  /**
   * Initiate the project
   */
  public function __construct($name, $project_name) {
    $this->name = $name;
    $this->project = $project_name;
    $this->server = 'localhost';
    $this->url = 'http://' . $name . '.' . $project_name . '.' . $this->server;
  }
}
