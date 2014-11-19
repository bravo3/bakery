<?php
namespace Bravo3\Bakery\Service\Cloner;

use Bravo3\Bakery\Entity\Repository;
use Bravo3\SSH\Shell;

class AbstractCloner
{
    /**
     * @var Repository
     */
    protected $repo;

    /**
     * @var Shell
     */
    protected $shell;

    /**
     * @var string
     */
    protected $output = '';

    /**
     * @var string
     */
    protected $prompt = '# ';

    /**
     * @var int
     */
    protected $timeout = 120;

    /**
     * Set repository
     *
     * @param Repository $repo
     * @return $this
     */
    public function setRepo(Repository $repo)
    {
        $this->repo = $repo;
        return $this;
    }

    /**
     * Set SSH shell
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
     * Get the output log
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Append to the output log
     *
     * @param string $txt
     */
    protected function addLog($txt)
    {
        $this->output .= $txt;
    }

    /**
     * Set console prompt
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
     * Get console prompt
     *
     * @return string
     */
    public function getPrompt()
    {
        return $this->prompt;
    }

    /**
     * Send and log a smart command
     *
     * @param string $cmd
     * @param int    $timeout
     */
    public function sendCommand($cmd, $timeout = 15)
    {
        if (substr($this->output, -1) !== "\n") {
            $this->addLog("\n");
        }
        $this->addLog($this->getPrompt().$cmd."\n");
        $this->addLog($this->shell->sendSmartCommand($cmd, true, $timeout, true));
    }

    /**
     * Get the connection timeout in seconds
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Set the connection timeout in seconds
     *
     * @param int $timeout
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }
}
