<?php
namespace Bravo3\Bakery\Operation;

use Bravo3\Bakery\Entity\Repository;
use Bravo3\Bakery\Enum\Phase;
use Bravo3\Bakery\Enum\RepositoryType;
use Bravo3\Bakery\Exception\UnexpectedValueException;
use Bravo3\Bakery\Service\Cloner\GitCloner;
use Bravo3\Bakery\Service\Cloner\RepositoryCloner;
use Bravo3\Bakery\Service\Cloner\SvnCloner;
use Bravo3\Bakery\Service\RemoteCredentialHelper;

class CodeCheckoutOperation extends AbstractOperation implements OperationInterface
{
    protected $private_key_file;
    const PASSPHRASE_HINT = "passphrase";

    /**
     * Run the operation
     *
     * @return bool
     */
    public function execute()
    {
        if (!($this->payload instanceof Repository)) {
            throw new UnexpectedValueException("Payload is not a repository");
        }

        // Cloner - will checkout/clone svn/git repos
        $cloner = $this->getCloner();
        if (!$cloner) {
            throw new UnexpectedValueException("Unable to retrieve cloner");
        }

        $this->status(Phase::CODE_CHECKOUT());

        // Will install private keys, passwords, etc for the repos
        $credential_helper = new RemoteCredentialHelper($this->shell);
        if ($this->payload->getPrivateKey()) {
            $installed_credentials = true;
            $this->logger->debug("Installing private key");
            $credential_helper->addPrivateKey($this->payload->getPrivateKey(), $this->payload->getPassword());
        } elseif ($this->payload->getPassword()) {
            $installed_credentials = true;
            $this->logger->debug("Installing password credentials");
            $hostname = parse_url($this->payload->getUri(), PHP_URL_HOST);
            $credential_helper->addPassword($hostname, $this->payload->getUsername(), $this->payload->getPassword());
        } else {
            $installed_credentials = false;
        }

        // Give the cloner it's dependencies
        $cloner->setRepo($this->payload);
        $cloner->setShell($this->shell);
        $cloner->setPrompt($this->getPrompt());

        // Clone away!
        $this->logger->debug("Cloning repository: ".$this->payload->getUri());

        try {
            $cloner->cloneRepo();
            $this->rawOutput($cloner->getOutput());
        } catch (\Exception $e) {
            $this->rawOutput($cloner->getOutput());
            $class = explode('\\', get_class($e));
            $this->logger->error(array_pop($class).': '.$e->getMessage());
            throw $e;
        } finally {
            // Remove credentials from the remote
            if ($installed_credentials) {
                $this->logger->debug("Removing installed credentials");
                $credential_helper->cleanup();
            }
        }

    }

    /**
     * Get the appropriate repository cloner
     *
     * @return RepositoryCloner
     */
    protected function getCloner()
    {
        switch ($this->payload->getRepositoryType()) {
            default:
                $this->status(Phase::ERROR(), 0, 0, "Unknown repository type: ".$this->payload->getRepositoryType());
                return null;
            case RepositoryType::GIT():
                return new GitCloner();
            case RepositoryType::SVN():
                return new SvnCloner();
        }
    }

}
