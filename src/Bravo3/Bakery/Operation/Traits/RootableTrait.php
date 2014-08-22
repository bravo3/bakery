<?php
namespace Bravo3\Bakery\Operation\Traits;

trait RootableTrait
{

    /**
     * @var bool
     */
    protected $run_as_root;

    /**
     * Define the root user execution flag
     *
     * @param boolean $run_as_root
     * @return $this
     */
    public function setRunAsRoot($run_as_root)
    {
        $this->run_as_root = $run_as_root;
        return $this;
    }

    /**
     * Get the root user execution flag
     *
     * @return boolean
     */
    public function getRunAsRoot()
    {
        return $this->run_as_root;
    }

} 