<?php
namespace Bravo3\Bakery\Tests\Resources;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class FileLogger extends AbstractLogger implements LoggerInterface
{

    protected $fp;

    protected $prefix;

    public function __construct($fn, $prefix = true)
    {
        $this->prefix = $prefix;
        $this->fp = fopen($fn, 'a');
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        $msg = $this->prefix ? ('['.$level.']: '.$message."\n") : ($message."\n");
        fwrite($this->fp, $msg);
    }

    function __destruct()
    {
        fclose($this->fp);
    }

}
