<?php
namespace Bravo3\Bakery\Operation;

use Bravo3\Bakery\Enum\PackagerType;
use Bravo3\Bakery\Enum\Phase;

class InstallPackagesOperation extends AbstractOperation implements OperationInterface
{
    const CMD_TIMEOUT = 60;

    /**
     * Run the operation
     *
     * @return bool
     */
    public function execute()
    {
        $this->status(Phase::INSTALL_PACKAGES());

        $this->enterRoot();
        $this->payload = (array)$this->payload;

        // Prep the packager, pick the base command
        switch ($this->packager_type) {
            default:
            case PackagerType::YUM():
                $cmd_base = 'yum -y install ';
                break;
            case PackagerType::APT():
                $cmd_base = 'apt-get -y install ';
                if (!$this->sendCommand("apt-get -y update", self::CMD_TIMEOUT)) {
                    $this->exitRoot();
                    return false;
                }
                break;
        }

        // Install all packages
        $package = implode(' ', $this->payload);
        if (!$this->sendCommand($cmd_base.$package, self::CMD_TIMEOUT)) {
            $this->exitRoot();
            return false;
        }

        $this->exitRoot();
        return true;
    }
} 