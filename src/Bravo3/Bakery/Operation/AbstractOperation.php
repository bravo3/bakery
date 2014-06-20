<?php
namespace Bravo3\Bakery\Operation;

use Bravo3\Bakery\Enum\PackagerType;
use Bravo3\SSH\Shell;
use Psr\Log\LoggerAwareTrait;

class AbstractOperation
{
    use LoggerAwareTrait;


    /**
     * @var PackagerType
     */
    protected $packager_type;

    /**
     * @var mixed
     */
    protected $payload;

    /**
     * @var Shell
     */
    protected $shell;

    /**
     * @var callable
     */
    protected $callback;

    function __construct($payload = null)
    {
        $this->payload = $payload;
    }

    /**
     * Set PackagerType
     *
     * @param PackagerType $packager_type
     * @return $this
     */
    public function setPackagerType(PackagerType $packager_type)
    {
        $this->packager_type = $packager_type;
        return $this;
    }

    /**
     * Set the operation payload
     *
     * @param mixed $payload
     * @return $this
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
        return $this;
    }

    /**
     * Set the SSH execution stream
     *
     * @param Shell $shell
     * @return $this
     */
    public function setShell(Shell $shell)
    {
        $this->shell = $shell;
        return $this;
    }

    /**
     * Set the status callback
     *
     * @param callable $callback
     * @return $this
     */
    public function setCallback(\Closure $callback = null)
    {
        $this->callback = $callback;
        return $this;
    }

}
