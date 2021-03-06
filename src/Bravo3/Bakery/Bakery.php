<?php
namespace Bravo3\Bakery;

use Bravo3\Bakery\Entity\Host;
use Bravo3\Bakery\Entity\Schema;
use Bravo3\Bakery\Enum\Phase;
use Bravo3\Bakery\Exception\ConnectionException;
use Bravo3\Bakery\Operation\OperationInterface;
use Bravo3\SSH\Connection;
use Bravo3\SSH\Enum\TerminalType;
use Bravo3\SSH\Enum\TerminalUnit;
use Bravo3\SSH\Terminal;
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
     * @param int    $terminal_width
     */
    public function bake(Schema $schema, $terminal_width = 180)
    {
        // Connect to host
        $con = $this->connect();
        if (!$con) {
            throw new ConnectionException("Unable to connect to bakery host");
        }

        // Get an SSH stream
        $this->status(Phase::ENVIRONMENT(), 0, 0, 'Configuring environment');
        $shell = $con->getShell(new Terminal($terminal_width, 25, TerminalUnit::CHARACTERS));
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
            $operation->setConnection($con);
            try {
                $operation->execute();
            } catch (\Exception $e) {
                $this->status(Phase::ERROR(), $pos + 1, $total, 'Operation failed ('.$e->getMessage().')');
                $con->disconnectChain();
                throw $e;
            }
        }

        $con->disconnectChain();
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
            0,
            0,
            "Connecting to target host ".$this->host->getHostname().':'.$this->host->getPort()
        );

        $con = new Connection($this->host->getHostname(), $this->host->getPort(), $this->host->getCredential());
        $con->setLogger($this->logger);

        if (!$con->connect()) {
            $this->status(Phase::ERROR(), 0, 0, "Failed to connect to target host");
            $con->disconnectChain();
            return null;
        }

        if (!$con->authenticate()) {
            $this->status(Phase::ERROR(), 0, 0, "Failed to authenticate on target host");
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
