<?php
namespace Bravo3\Bakery\Operation;

interface RootableInterface
{

    /**
     * Define the root user execution flag
     *
     * @param boolean $run_as_root
     * @return $this
     */
    public function setRunAsRoot($run_as_root);

    /**
     * Get the root user execution flag
     *
     * @return boolean
     */
    public function getRunAsRoot();

}
