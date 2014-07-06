<?php
namespace Bravo3\Bakery\Operation\Traits;

use Bravo3\Bakery\Exception\ApplicationException;
use Bravo3\SSH\Shell;

/**
 * Yum specific functions
 *
 * @property Shell $shell
 */
trait YumTrait
{

    /**
     * Wait for yum to finish
     *
     * @param int $timeout
     * @param int $retry
     */
    function waitForYum($timeout = 30, $retry = 2)
    {
        $retry = max($retry, (int)$retry);
        $start = time();
        $cmd = 'ps a | grep yum | grep -v grep';
        while ((bool)$this->shell->sendSmartCommand($cmd, true, 3, true)) {
            if (time() > $start + $timeout) {
                throw new ApplicationException("Timeout waiting for yum to become available");
            }
            sleep($retry);
        }
    }

} 