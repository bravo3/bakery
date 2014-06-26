<?php
namespace Bravo3\Bakery\Operation;

class ScriptOperation extends AbstractOperation implements OperationInterface
{

    /**
     * @var int New content timeout in seconds
     */
    protected $timeout = 600;

    /**
     * Run the operation
     *
     * @return bool
     */
    public function execute()
    {
        if (!is_array($this->payload)) {
            $this->payload = explode("\n", $this->payload);
        }

        foreach ($this->payload as $command) {
            $this->output($this->shell->sendSmartCommand($command, false, $this->timeout, true));
        }

        return true;
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