<?php
namespace Bravo3\Bakery\Operation;

class ScriptOperation extends AbstractOperation implements OperationInterface
{

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
            $this->output($this->shell->sendSmartCommand($command, false));
        }

        return true;
    }

}