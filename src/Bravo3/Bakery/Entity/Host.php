<?php
namespace Bravo3\Bakery\Entity;

use Bravo3\SSH\Credentials\SSHCredential;

class Host
{

    /**
     * @var string
     */
    protected $hostname;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var SSHCredential
     */
    protected $credential;

    function __construct($hostname, $port = 22, SSHCredential $credential = null)
    {
        $this->hostname   = $hostname;
        $this->port       = $port;
        $this->credential = $credential;
    }

    /**
     * Set Credential
     *
     * @param \Bravo3\SSH\Credentials\SSHCredential $credential
     * @return $this
     */
    public function setCredential($credential)
    {
        $this->credential = $credential;
        return $this;
    }

    /**
     * Get Credential
     *
     * @return \Bravo3\SSH\Credentials\SSHCredential
     */
    public function getCredential()
    {
        return $this->credential;
    }

    /**
     * Set Hostname
     *
     * @param string $hostname
     * @return $this
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;
        return $this;
    }

    /**
     * Get Hostname
     *
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * Set Port
     *
     * @param int $port
     * @return $this
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * Get Port
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

}
