<?php
namespace Bravo3\Bakery\Service\Cloner;

use Bravo3\Bakery\Entity\Repository;
use Bravo3\SSH\Shell;

interface RepositoryCloner
{
    /**
     * Set repository
     *
     * @param Repository $repo
     * @return $this
     */
    public function setRepo(Repository $repo);

    /**
     * Set SSH shell
     *
     * @param Shell $shell
     * @return $this
     */
    public function setShell(Shell $shell);

    /**
     * Clone and checkout a revision/tag/branch
     */
    public function checkout();

    /**
     * Get the output log
     *
     * @return string
     */
    public function getOutput();

    /**
     * Set console prompt
     *
     * @param string $prompt
     * @return $this
     */
    public function setPrompt($prompt);

    /**
     * Get the connection timeout in seconds
     *
     * @return int
     */
    public function getTimeout();
}
