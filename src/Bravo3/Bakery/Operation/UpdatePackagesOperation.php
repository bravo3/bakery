<?php
namespace Bravo3\Bakery\Operation;

use Bravo3\Bakery\Enum\PackagerType;
use Bravo3\Bakery\Enum\Phase;
use Bravo3\Bakery\Exception\ApplicationException;
use Bravo3\Bakery\Operation\Traits\YumTrait;

class UpdatePackagesOperation extends AbstractOperation implements OperationInterface
{
    use YumTrait;

    const CMD_TIMEOUT = 60;

    /**
     * Run the operation
     */
    public function execute()
    {
        $this->enterRoot();

        $this->status(Phase::UPDATE_PACKAGES());

        switch ($this->packager_type) {
            default:
            case PackagerType::YUM():
                $this->waitForYum(self::CMD_TIMEOUT);
                $cmds           = ['yum -y update'];
                $allowed_errors = ['Existing lock '];
                break;
            case PackagerType::APT():
                $cmds           = ['apt-get -y update', 'apt-get -y upgrade'];
                $allowed_errors = ['Extracting templates from packages:'];
                break;
        }

        foreach ($cmds as $cmd) {
            if (!$this->sendCommand($cmd, self::CMD_TIMEOUT, $allowed_errors)) {
                $this->exitRoot();
                throw new ApplicationException("System packager failed during update");
            }
        }

        $this->exitRoot();
    }
} 