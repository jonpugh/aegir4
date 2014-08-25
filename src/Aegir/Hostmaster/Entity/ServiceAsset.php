<?php

namespace Aegir\Hostmaster\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ServiceAsset
 */
class ServiceAsset
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $service;

    /**
     * @var integer
     */
    private $environment;

    /**
     * @var array
     */
    private $data;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set service
     *
     * @param integer $service
     * @return ServiceAsset
     */
    public function setService($service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service
     *
     * @return integer 
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set environment
     *
     * @param integer $environment
     * @return ServiceAsset
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * Get environment
     *
     * @return integer 
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Set data
     *
     * @param array $data
     * @return ServiceAsset
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return array 
     */
    public function getData()
    {
        return $this->data;
    }
}
