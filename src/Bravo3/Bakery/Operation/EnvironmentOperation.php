<?php
namespace Bravo3\Bakery\Operation;

use Bravo3\SSH\Enum\ShellType;


/**
 * Sets all environment variables on the system
 */
class EnvironmentOperation extends AbstractOperation implements OperationInterface
{

    /**
     * @var array<string, string>
     */
    protected $variables;

    /**
     * @var ShellType
     */
    protected $shell_type;

    function __construct(array $variables = [])
    {
        $this->variables  = $variables;
        $this->shell_type = ShellType::UNKNOWN();
    }

    /**
     * Run the operation
     *
     * @return bool
     */
    public function execute()
    {
        $this->shell_type = $this->shell->getShellType();
        $this->logger->debug("Remote shell is identifying as ".$this->shell_type->value());

        foreach ($this->variables as $key => $value) {
            switch ($this->shell_type) {
                // Bourne-shell compatibles
                default:
                    $cmd = 'export '.$key.'="'.$value.'"';
                    break;
                // C-shell compatibles
                case ShellType::CSH():
                case ShellType::TCSH():
                    $cmd = 'set '.$key.'="'.$value.'"';
            }

            $output = $this->shell->sendSmartCommand($cmd, false);
            $this->logger->info($this->getPrompt().$this->cleanOutout($output));
        }

        return true;
    }

    /**
     * After execution the remote shell name is available
     *
     * @return ShellType
     */
    public function getShellType()
    {
        return $this->shell_type;
    }

    /**
     * Set Variables
     *
     * @param array <string, string> $variables
     * @return $this
     */
    public function setVariables(array $variables)
    {
        $this->variables = $variables;
        return $this;
    }

    /**
     * Get Variables
     *
     * @return array<string, string>
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * Set a variable
     *
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function addVariable($key, $value)
    {
        $this->variables[$key] = $value;
        return $this;
    }

    /**
     * Get a variable
     *
     * @param string $key
     * @param string $default
     * @return string
     */
    public function getVariable($key, $default = null)
    {
        return isset($this->variables[$key]) ? $this->variables[$key] : $default;
    }


}
 