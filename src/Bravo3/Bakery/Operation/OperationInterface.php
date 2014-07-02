<?php
namespace Bravo3\Bakery\Operation;

use Bravo3\Bakery\Enum\PackagerType;
use Bravo3\SSH\Connection;
use Bravo3\SSH\Shell;
use Psr\Log\LoggerAwareInterface;

interface OperationInterface extends LoggerAwareInterface
{
    /**
     * Set PackagerType
     *
     * @param PackagerType $packager_type
     * @return $this
     */
    public function setPackagerType(PackagerType $packager_type);

    /**
     * Set the operation payload
     *
     * @param mixed $payload
     * @return $this
     */
    public function setPayload($payload);

    /**
     * Set the SSH execution stream
     *
     * @param Shell $stream
     * @return $this
     */
    public function setShell(Shell $shell);

    /**
     * Set SSH connection
     *
     * @param Connection $connection
     * @return $this
     */
    public function setConnection($connection);

    /**
     * Set the status callback
     *
     * @param callable $callback
     * @return $this
     */
    public function setCallback(\Closure $callback = null);

    /**
     * Run the operation
     *
     * @return bool
     */
    public function execute();

    /**
     * Define the path to save log files in
     *
     * @param string $path
     * @return $this
     */
    public function setLogPrefix($path);
}
