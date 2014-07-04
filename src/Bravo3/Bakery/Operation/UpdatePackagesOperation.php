<?php
namespace Bravo3\Bakery\Operation;

use Bravo3\Bakery\Enum\PackagerType;
use Bravo3\Bakery\Enum\Phase;

class UpdatePackagesOperation extends AbstractOperation implements OperationInterface
{
    const CMD_TIMEOUT = 60;

    /**
     * Run the operation
     *
     * @return bool
     */
    public function execute()
    {
        $this->enterRoot();

        $this->status(Phase::UPDATE_PACKAGES());

        switch ($this->packager_type) {
            default:
            case PackagerType::YUM():
                $cmds           = ['yum -y update'];
                $allowed_errors = [];
                break;
            case PackagerType::APT():
                $cmds           = ['apt-get -y update', 'apt-get -y upgrade'];
                $allowed_errors = ['Extracting templates from packages: 100%'];
                break;
        }

        foreach ($cmds as $cmd) {
            if (!$this->sendCommand($cmd, self::CMD_TIMEOUT, $allowed_errors)) {
                $this->exitRoot();
                return false;
            }
        }

        $this->exitRoot();
        return true;
    }
} 