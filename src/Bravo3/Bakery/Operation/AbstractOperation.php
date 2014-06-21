<?php
namespace Bravo3\Bakery\Operation;

use Bravo3\Bakery\Enum\PackagerType;
use Bravo3\SSH\Shell;
use Psr\Log\LoggerAwareTrait;

class AbstractOperation
{
    use LoggerAwareTrait;


    /**
     * @var PackagerType
     */
    protected $packager_type;

    /**
     * @var mixed
     */
    protected $payload;

    /**
     * @var Shell
     */
    protected $shell;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var string
     */
    protected $prompt = '# ';


    function __construct($payload = null)
    {
        $this->payload = $payload;
    }

    /**
     * Set PackagerType
     *
     * @param PackagerType $packager_type
     * @return $this
     */
    public function setPackagerType(PackagerType $packager_type)
    {
        $this->packager_type = $packager_type;
        return $this;
    }

    /**
     * Set the operation payload
     *
     * @param mixed $payload
     * @return $this
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
        return $this;
    }

    /**
     * Set the SSH execution stream
     *
     * @param Shell $shell
     * @return $this
     */
    public function setShell(Shell $shell)
    {
        $this->shell = $shell;
        return $this;
    }

    /**
     * Set the status callback
     *
     * @param callable $callback
     * @return $this
     */
    public function setCallback(\Closure $callback = null)
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * Remove the smart console marker
     *
     * @param string $output
     * @return string
     */
    protected function cleanOutout($output)
    {
        return str_replace("\r\n".$this->shell->getSmartMarker(), '', $output);
    }

    /**
     * Set the prompt marker - this is just a prefix to identify commands from output
     *
     * @param string $prompt
     * @return $this
     */
    public function setPrompt($prompt)
    {
        $this->prompt = $prompt;
        return $this;
    }

    /**
     * Get the prompt marker - this is just a prefix to identify commands from output
     *
     * @return string
     */
    public function getPrompt()
    {
        return $this->prompt;
    }

}
