<?php
namespace Bravo3\Bakery\Operation;

use Bravo3\Bakery\Enum\PackagerType;
use Bravo3\Bakery\Enum\Phase;
use Bravo3\Bakery\Exception\ApplicationException;
use Bravo3\Bakery\Operation\Traits\YumTrait;

class InstallPackagesOperation extends AbstractOperation implements OperationInterface
{
    use YumTrait;

    const CMD_TIMEOUT = 60;

    /**
     * Run the operation
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
                $this->waitForYum(self::CMD_TIMEOUT);
                $cmd_base = 'yum -y install ';
                break;
            case PackagerType::APT():
                $cmd_base = 'apt-get -y install ';
                if (!$this->sendCommand("apt-get -y update", self::CMD_TIMEOUT)) {
                    $this->exitRoot();
                    throw new ApplicationException("Update failed");
                }
                break;
        }

        // Install all packages
        $package = implode(' ', $this->payload);
        if (!$this->sendCommand($cmd_base.$package, self::CMD_TIMEOUT)) {
            $this->exitRoot();
            throw new ApplicationException("System packages install failed");
        }

        $this->exitRoot();
    }
} 