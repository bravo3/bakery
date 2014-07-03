<?php
namespace Bravo3\Bakery\Exception;

class TransportException extends \RuntimeException implements BakeryException
{
    const SEND    = 0;
    const RECEIVE = 1;

    protected $local;

    protected $remote;

    protected $mode;

    protected $direction;

    public function __construct($msg, $local, $remote, $mode, $direction, \Exception $parent_exception = null)
    {
        parent::__construct($msg, 0, $parent_exception);
        $this->local     = $local;
        $this->remote    = $remote;
        $this->mode      = $mode;
        $this->direction = $direction;
    }

    /**
     * Get Direction
     *
     * @return mixed
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * Get Local
     *
     * @return mixed
     */
    public function getLocal()
    {
        return $this->local;
    }

    /**
     * Get Mode
     *
     * @return mixed
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Get Remote
     *
     * @return mixed
     */
    public function getRemote()
    {
        return $this->remote;
    }

}
