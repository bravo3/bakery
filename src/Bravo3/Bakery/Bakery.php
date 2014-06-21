<?php
namespace Bravo3\Bakery;

use Bravo3\Bakery\Entity\Host;
use Bravo3\Bakery\Entity\Schema;
use Bravo3\Bakery\Enum\Phase;
use Bravo3\Bakery\Operation\OperationInterface;
use Bravo3\SSH\Connection;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Bakery implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var \Closure
     */
    protected $status_callback;

    /**
     * @var LoggerInterface
     */
    protected $output;

    /**
     * @var Host
     */
    protected $host;

    function __construct(Host $host, LoggerInterface $output = null, \Closure $status_callback = null)
    {
        $this->logger          = new NullLogger();
        $this->host            = $host;
        $this->output          = $output ? : new NullLogger();
        $this->status_callback = $status_callback;
        $this->tunnels         = [];
    }

    /**
     * Set Host
     *
     * @param Host $host
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Get Host
     *
     * @return Host
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set StatusCallback
     *
     * @param callable $status_callback
     * @return $this
     */
    public function setStatusCallback(\Closure $status_callback)
    {
        $this->status_callback = $status_callback;
        return $this;
    }

    /**
     * Get StatusCallback
     *
     * @return callable
     */
    public function getStatusCallback()
    {
        return $this->status_callback;
    }

    /**
     * Set the LoggerInterface that will receive console output during the bake process
     *
     * @param LoggerInterface $output
     * @return $this
     */
    public function setOutput($output)
    {
        $this->output = $output;
        return $this;
    }

    /**
     * Get the LoggerInterface that will receive console output during the bake process
     *
     * @return LoggerInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Bake the host
     *
     * @param Schema $schema
     * @return bool
     */
    public function bake(Schema $schema)
    {
        // Connect to host
        $con = $this->connect();
        if (!$con) {
            return false;
        }

        // Get an SSH stream
        $shell = $con->getShell();
        $this->status(Phase::ENVIRONMENT(), 1, 1, 'Configuring environment');
        $shell->setSmartConsole();

        // Traverse operations
        $total = $schema->getOperationCount();
        /** @var OperationInterface $operation */
        foreach ($schema as $pos => $operation) {
            $this->status(Phase::OPERATION(), $pos + 1, $total, 'Executing '.$this->getOperationName($operation));
            $operation->setLogger($this->output);
            $operation->setCallback($this->status_callback);
            $operation->setPackagerType($schema->getPackagerType());
            $operation->setShell($shell);
            if (!$operation->execute()) {
                $this->status(Phase::ERROR(), $pos + 1, $total, 'Operation failed, aborting');
                return false;
            }
        }

        $con->disconnectChain();
        return true;
    }

    /**
     * Get a human readable name from the operation class
     *
     * @param OperationInterface $operation
     * @return string
     */
    protected function getOperationName(OperationInterface $operation)
    {
        $operation_name = explode('\\', get_class($operation));
        $operation_name = array_pop($operation_name);
        return strtolower(preg_replace("/(([a-z])([A-Z])|([A-Z])([A-Z][a-z]))/", "\\2\\4 \\3\\5", $operation_name));
    }

    /**
     * Connect to the host via all tunnel nodes
     *
     * @return Connection|null
     */
    protected function connect()
    {
        // Connect to target host
        $this->status(
            Phase::CONNECTION(),
            1,
            1,
            "Connecting to target host ".$this->host->getHostname().':'.$this->host->getPort()
        );

        $con = new Connection($this->host->getHostname(), $this->host->getPort(), $this->host->getCredential());
        $con->setLogger($this->logger);

        if (!$con->connect()) {
            $this->status(Phase::ERROR(), 1, 1, "Failed to connect to target host");
            $con->disconnectChain();
            return null;
        }

        if (!$con->authenticate()) {
            $this->status(Phase::ERROR(), 1, 1, "Failed to authenticate on target host");
            $con->disconnectChain();
            return null;
        }

        return $con;
    }


    /**
     * Report status to the log and callback
     *
     * @param Phase  $phase
     * @param int    $step
     * @param int    $total
     * @param string $message
     */
    protected function status(Phase $phase, $step, $total, $message)
    {
        $this->logger->info('['.$phase->value().'] '.$message);

        if ($this->status_callback) {
            $closure = $this->status_callback;
            $closure($phase, $step, $total, $message);
        }
    }

}
