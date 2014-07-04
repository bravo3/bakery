<?php
namespace Bravo3\Bakery\Operation;

use Bravo3\Bakery\Enum\PackagerType;
use Bravo3\Bakery\Enum\Phase;
use Bravo3\SSH\Connection;
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
     * @var Connection
     */
    protected $connection;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var string
     */
    protected $prompt = '# ';

    /**
     * This is a path + file prefix on the REMOTE to send stderr output to for smart commands.
     * It will then be scanned to detect any errors a command may throw.
     *
     * @var string
     */
    protected $log_prefix = '/tmp/bakery';

    /**
     * @var int
     */
    protected static $log_index = 0;


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
     * Set SSH connection
     *
     * @param Connection $connection
     * @return $this
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * Get SSH connection
     *
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
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

    /**
     * Remove the smart console marker
     *
     * @param string $output
     * @return string
     */
    protected function cleanOutput($output)
    {
        return str_replace("\n".$this->shell->getSmartMarker(), '', $this->normaliseEol($output));
    }

    /**
     * Set the prompt marker - this is just a prefix to identify commands from output
     *
     * @param string $prompt
     * @return $this
     */
    public function setPrompt($prompt)
    {
        $this->prompt = $prompt;
        return $this;
    }

    /**
     * Get the prompt marker - this is just a prefix to identify commands from output
     *
     * @return string
     */
    public function getPrompt()
    {
        return $this->prompt;
    }

    /**
     * Define the path and prefix to save log files in
     *
     * @param string $path eg: /tmp/bakery
     * @return $this
     */
    public function setLogPrefix($path)
    {
        $this->log_prefix = $path;
        return $this;
    }

    /**
     * Send a command, logging error output to the filesystem
     *
     * The output of the commands will be send to the logger.
     *
     * @param string   $cmd
     * @param int      $timeout
     * @param string[] $allowed_errors Convert these errors to notices
     * @return bool True if the command completes without error
     */
    protected function sendCommand($cmd, $timeout = 15, array $allowed_errors = [])
    {
        $this->logger->debug("Exec: ".$cmd);
        $log_file = $this->log_prefix.'-'.(self::$log_index++).'.error.log';
        $output   = $this->shell->sendSmartCommand($cmd.' 2> '.$log_file, false, $timeout, true);
        $errors   = trim($this->shell->sendSmartCommand('cat '.$log_file, true, 3, true));
        $this->output($output);

        if ($errors) {
            if (in_array($errors, $allowed_errors)) {
                // Acceptable errors
                $this->logger->notice($errors);
                return true;
            } else {
                // Unacceptable errors
                $this->logger->debug("Command returned errors:");
                $this->error($errors);
                return false;
            }
        } else {
            // No errors
            return true;
        }
    }

    /**
     * Log output
     *
     * @param string $output
     * @return $this
     */
    protected function output($output)
    {
        $this->logger->info($this->getPrompt().$this->cleanOutput($output));
        return $this;
    }

    /**
     * Log output, but do not prefix the prompt or clean the output
     *
     * @param $output
     * @return $this
     */
    protected function rawOutput($output)
    {
        $this->logger->info($output);
        return $this;
    }

    /**
     * Log error output
     *
     * @param string $output
     * @return $this
     */
    protected function error($output)
    {
        $this->logger->error($this->cleanOutput($output));
        return $this;
    }

    /**
     * Use 'sudo -i' to enter root
     */
    protected function enterRoot()
    {
        $this->shell->sendln("sudo -i");
        $output = $this->shell->waitForContent(0.5);
        $this->output($output);
        $this->shell->setSmartConsole();
    }

    /**
     * Use 'exit' to exit root
     */
    protected function exitRoot()
    {
        $this->sendCommand('exit', 2, ['logout']);
    }

    /**
     * Ensure all line-endings are in the form \n
     *
     * @param string $str
     * @return string
     */
    protected function normaliseEol($str)
    {
        return str_replace("\r\n", "\n", $str);
    }


    /**
     * Report status to the log and callback
     *
     * From an operation, the Phase should only ever be Phase::SUB_OPERATION() or Phase::ERROR().
     *
     * @param Phase  $phase
     * @param int    $step
     * @param int    $total
     * @param string $message
     */
    protected function status(Phase $phase, $step = 0, $total = 0, $message = '')
    {
        $this->logger->info('['.$phase->value().'] '.$message);

        if ($this->callback) {
            $closure = $this->callback;
            $closure($phase, $step, $total, $message);
        }
    }

}
