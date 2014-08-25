<?php

namespace Aegir\Provision\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Deployment
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Deployment
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="datetime", type="datetime")
     */
    private $datetime;

    /**
     * @var integer
     *
     * @ORM\Column(name="environment", type="integer")
     */
    private $environment;

    /**
     * @var string
     *
     * @ORM\Column(name="git_ref", type="string", length=255)
     */
    private $gitRef;


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
     * Set datetime
     *
     * @param \DateTime $datetime
     * @return Deployment
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;

        return $this;
    }

    /**
     * Get datetime
     *
     * @return \DateTime 
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * Set environment
     *
     * @param integer $environment
     * @return Deployment
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
     * Set gitRef
     *
     * @param string $gitRef
     * @return Deployment
     */
    public function setGitRef($gitRef)
    {
        $this->gitRef = $gitRef;

        return $this;
    }

    /**
     * Get gitRef
     *
     * @return string 
     */
    public function getGitRef()
    {
        return $this->gitRef;
    }
}
