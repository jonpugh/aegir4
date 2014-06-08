<?php

namespace Aegir\Provision\Model;

class ServerCollection
{
  /**
   * @var Server[]
   */
  public $servers;

  /**
   * @var integer
   */
  public $offset;

  /**
   * @var integer
   */
  public $limit;

  /**
   * @param Server[]  $notes
   * @param integer $offset
   * @param integer $limit
   */
  public function __construct($servers = array(), $offset = null, $limit = null)
  {
    $this->servers = $servers;
    $this->offset = $offset;
    $this->limit = $limit;
  }
}
