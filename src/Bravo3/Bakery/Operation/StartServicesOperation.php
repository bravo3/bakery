<?php
namespace Bravo3\Bakery\Operation;

use Bravo3\Bakery\Enum\Phase;
use Bravo3\Bakery\Enum\ServiceType;
use Bravo3\Bakery\Exception\ApplicationException;
use Bravo3\Bakery\Exception\UnexpectedValueException;
use Bravo3\Bakery\Operation\Traits\YumTrait;

class StartServicesOperation extends AbstractOperation implements OperationInterface
{
    const DEFAULT_SERVICE_TYPE = 'sysvinit';

    /**
     * Run the operation
     */
    public function execute()
    {
        $this->enterRoot();

        $this->status(Phase::RUN_SERVICES());

        $services = (array)$this->payload;

        foreach ($services as $service) {
            $service = trim($service);
            if (!$service) {
                continue;
            }

            // Break out the service type from the service name
            // eg: "upstart/networking" or "systemd/apache2"
            $parts = explode('/', $service, 2);

            if (count($parts) == 2) {
                $manager      = $parts[0];
                $service_name = $parts[1];
            } else {
                $manager      = self::DEFAULT_SERVICE_TYPE;
                $service_name = $parts[0];
            }

            $service_type = ServiceType::memberByKey(strtoupper($manager));
            $allowed_errors = ['start: Job is already running: '];

            switch ($service_type) {
                default:
                    throw new UnexpectedValueException("Unknown service type: ".$manager);
                case ServiceType::SYSTEMD():
                    $cmd = 'systemctl start '.$service_name.'.service';
                    break;
                case ServiceType::UPSTART():
                    $cmd = 'start '.$service_name;
                    $allowed_errors[] = 'start: Job is already running: ';
                    break;
                case ServiceType::SYSVINIT():
                    $cmd = 'service '.$service_name.' start';
                    break;
            }

            if (!$this->sendCommand($cmd, 15, $allowed_errors)) {
                $this->exitRoot();
                throw new ApplicationException("Failed to start service [".$service_name."]");
            }

        }

        $this->exitRoot();
    }
} 