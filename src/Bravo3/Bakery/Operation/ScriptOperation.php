<?php
namespace Bravo3\Bakery\Operation;

use Bravo3\Bakery\Enum\Phase;
use Bravo3\Bakery\Operation\Traits\RootableTrait;

class ScriptOperation extends AbstractOperation implements OperationInterface, RootableInterface
{
    use RootableTrait;

    /**
     * @var int New content timeout in seconds
     */
    protected $timeout = 600;

    /**
     * Run the operation
     */
    public function execute()
    {
        if (!is_array($this->payload)) {
            $this->payload = explode("\n", $this->payload);
        }

        if ($this->run_as_root) {
            $this->enterRoot();
        }

        $this->status(Phase::SCRIPT());

        foreach ($this->payload as $command) {
            $this->output($this->shell->sendSmartCommand($command, false, $this->timeout, true));
        }

        if ($this->run_as_root) {
            $this->exitRoot();
        }
    }

    /**
     * Set new content timeout in seconds
     *
     * @param int $timeout
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * Get new content timeout in seconds
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

}