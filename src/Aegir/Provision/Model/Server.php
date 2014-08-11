<?php

namespace Aegir\Provision\Model;

class Server {

  /**
   * @var int
   */
  public $id;

  /**
   * @var string
   */
  public $secret;

  /**
   * @var string
   */
  public $hostname;

  /**
   * @var array
   */
  public $ip_addresses;

  /**
   * @var Service[]
   */
  public $services;

  /**
   * Initiate the server.
   *
   * The first time this is initiated
   */
  public function __construct($hostname, $ip_addresses = '', $services = array()) {
    $this->hostname = $hostname;


    if (!empty($hostname) && $ip_addresses == '') {
      $this->ip_addresses[] = gethostbyname($hostname);
    }
    else {
      $this->ip_addresses[] = $ip_addresses;
    }

    $this->services = $services;

    if (!empty($this->hostname)){
      print_r($this);
      die;
    }
  }
}
