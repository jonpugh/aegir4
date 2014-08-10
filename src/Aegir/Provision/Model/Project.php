<?php

namespace Aegir\Provision\Model;

class Project {

  /**
   * @var string
   * Must be unique within Aegir.
   */
  public $name;

  /**
   * @var string
   * Git repo URL, Makefile URL, or Composer.json URL?
   */
  public $source_url;

  /**
   * @var Environment[]
   */
  public $environments;

  /**
   * Initiate the project
   */
  public function __construct($name, $source_url) {
    $this->name = $name;
    $this->source_url = $source_url;

    // Create dummy environments
    $this->environments['dev'] = new Environment('dev', $name, 'master');
    $this->environments['test'] = new Environment('test', $name, 'r0.1.1');
    $this->environments['live'] = new Environment('live', $name, 'r0.1.2');
  }
}
