<?php
namespace Bravo3\Bakery\Tests\Resources;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class FileLogger extends AbstractLogger implements LoggerInterface
{

    protected $fp;

    protected $prefix;

    protected $colourise;

    public function __construct($fn, $prefix = false, $colourise = false)
    {
        $this->prefix    = $prefix;
        $this->colourise = $colourise;
        $this->fp        = fopen($fn, 'a');
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
        if ($this->colourise) {
            switch ($level) {
                default:
                case LogLevel::INFO:
                    break;
                case LogLevel::ALERT:
                case LogLevel::CRITICAL:
                case LogLevel::EMERGENCY:
                case LogLevel::ERROR:
                    $message = "\033[31m".$message."\033[0m";
                    break;
                case LogLevel::DEBUG:
                    $message = "\033[34m".$message."\033[0m";
                    break;
                case LogLevel::NOTICE:
                    $message = "\033[33m".$message."\033[0m";
                    break;
            }
        }

        $msg = $this->prefix ? ('['.$level.']: '.$message."\n") : ($message."\n");

        fwrite($this->fp, $msg);
    }

    function __destruct()
    {
        fclose($this->fp);
    }

}
