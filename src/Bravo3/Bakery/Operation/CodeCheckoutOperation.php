<?php
namespace Bravo3\Bakery\Operation;

use Bravo3\Bakery\Entity\Repository;
use Bravo3\Bakery\Enum\Phase;
use Bravo3\Bakery\Enum\RepositoryType;
use Bravo3\Bakery\Services\GitCloner;
use Bravo3\Bakery\Services\RepositoryCloner;
use Bravo3\Bakery\Services\SvnCloner;

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
            $this->status(Phase::ERROR(), 0, 0, "Payload is not a repository");
            $this->cleanup();
            return false;
        }

        if ($this->payload->getPrivateKey()) {
            if (!$this->injectPrivateKey()) {
                $this->cleanup();
                return false;
            }
        }

        $cloner = $this->getCloner();
        if (!$cloner) {
            $this->cleanup();
            return false;
        }

        $cloner->setRepo($this->payload);
        $cloner->setShell($this->shell);
        $cloner->setPrompt($this->getPrompt());

        $result = $cloner->checkout();
        $this->rawOutput($cloner->getOutput());
        $this->cleanup();

        return $result;
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

    /**
     * SCP the private key to the remote and add it to the SSH agent
     *
     * @return bool
     */
    protected function injectPrivateKey()
    {
        // Private key required, we'll need it on the filesystem for SCP to work (doh!)
        $fn                     = tempnam(sys_get_temp_dir(), '');
        $this->private_key_file = basename($fn);
        file_put_contents($fn, $this->payload->getPrivateKey());

        // Send to remote
        $this->connection->scpSend($fn, $this->private_key_file, 0600);

        // For security, zero out the file so the private key can't be recovered
        file_put_contents($fn, str_repeat(chr(0), strlen($this->payload->getPrivateKey())));
        unlink($fn);

        // Add to SSH agent on remote
        if ($this->payload->getPassword()) {
            // ssh-add will ask for a password
            $this->shell->sendln('ssh-add '.$this->private_key_file);
            $out = $this->shell->readUntilEndMarker("Enter passphrase for ".$this->private_key_file.": ", 1, true);
            $this->output($out);
            if (stripos($out, self::PASSPHRASE_HINT) !== false) {
                $this->shell->sendln($this->payload->getPassword());
            }
        } else {
            $out = $this->sendCommand('ssh-add '.$this->private_key_file);
            $this->output($out);
            if (stripos($out, self::PASSPHRASE_HINT) !== false) {
                $this->status(Phase::ERROR(), 0, 0, "Private key requires a password, but none provided");
                $this->logger->error("no password available");
                return false;
            }
        }

        return true;
    }

    /**
     * Any cleanup required before completing/aborting
     */
    protected function cleanup()
    {
        if ($this->private_key_file) {
            $this->sendCommand("rm -f ".$this->private_key_file);
        }
    }
}
