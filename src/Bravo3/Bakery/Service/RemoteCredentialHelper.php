<?php
namespace Bravo3\Bakery\Service;

use Bravo3\Bakery\Exception\CredentialException;
use Bravo3\Bakery\Exception\TransportException;
use Bravo3\SSH\Shell;

/**
 * This service will install credentials on the remote ready for use by applications that read from private keys
 * or the systems .netrc file. When the #cleanup() function is called, all files sent to the remote will be deleted.
 *
 * This service will establish its own shell when required.
 *
 * NB: This will overwrite and delete the remote .netrc file.
 */
class RemoteCredentialHelper
{
    const PASSPHRASE_HINT = 'passphrase';
    const CMD_TIMEOUT     = 3;

    /**
     * @var string[]
     */
    protected $files = [];

    /**
     * @var Shell
     */
    protected $shell;

    function __construct(Shell $shell)
    {
        $this->shell = $shell;
    }


    /**
     * Install a username/password against a hostname
     *
     * TIP: $hostname = parse_url($uri, PHP_URL_HOST)
     *
     * @param string $hostname
     * @param string $username
     * @param string $password
     * @throws TransportException
     */
    public function addPassword($hostname, $username, $password)
    {
        // Prepare a .netrc file
        $netrc = 'machine '.$hostname.' login '.$username.' password '.$password."\n";
        $fn    = '.netrc';
        $this->createTextFile($netrc, $fn);
        $this->files[] = $fn;
    }

    /**
     * Install a private key on the remote
     *
     * @param string $key
     * @param string $password
     * @throws TransportException
     * @throws CredentialException
     */
    public function addPrivateKey($key, $password = null)
    {
        // Send to remote
        $fn = md5($key);
        $this->createTextFile($key, $fn);
        $this->files[] = $fn;

        // Check we have the SSH Agent running, start if not
        $agent_pid = $this->shell->sendSmartCommand('eval `ssh-agent -s`', true, self::CMD_TIMEOUT, true);
        if (!preg_match('/Agent pid [0-9]+/', $agent_pid)) {
            throw new CredentialException("SSH Agent required for key pair authentication: ".$agent_pid);
        }

        // Add to SSH agent on remote
        if ($password) {
            // ssh-add will ask for a password
            $this->shell->sendln('ssh-add '.$fn);
            $out = $this->shell->readUntilExpression("/Enter passphrase for .*: /", self::CMD_TIMEOUT, true);
            if (stripos($out, self::PASSPHRASE_HINT) !== false) {
                $this->shell->sendln($password);
                $this->shell->readUntilEndMarker($this->shell->getSmartMarker());
            }
        } else {
            $out = $this->shell->sendSmartCommand('ssh-add '.$fn, true, self::CMD_TIMEOUT, true);
            if (stripos($out, self::PASSPHRASE_HINT) !== false) {
                throw new CredentialException("Private key requires a password, but none provided");
            }
        }
    }

    /**
     * Create a text file on the remote using echo and IO redirection
     *
     * @param string $txt  Text content
     * @param string $fn   Remote file name
     * @param string $mode Permissions in an octal string, set to null to assume permissions from the umask
     */
    protected function createTextFile($txt, $fn, $mode = '600')
    {
        $this->shell->sendSmartCommand('echo "'.$txt.'" > "'.$fn.'"', true, self::CMD_TIMEOUT);
        if ($mode) {
            $this->shell->sendSmartCommand('chmod '.$mode.' "'.$fn.'"', true, self::CMD_TIMEOUT);
        }
    }

    /**
     * Zero out a remote file and then delete it
     *
     * @param string $fn
     */
    protected function secureDeleteFile($fn)
    {
        $this->shell->sendSmartCommand(
            'dd if=/dev/zero of="'.$fn.'" count=1 bs=`wc -c < "'.$fn.'"`',
            true,
            self::CMD_TIMEOUT,
            true
        );
        $this->shell->sendSmartCommand('rm -f "'.$fn.'"', true, self::CMD_TIMEOUT, true);
    }

    /**
     * Remove all files sent to the remote
     */
    public function cleanup()
    {
        foreach ($this->files as $file) {
            $this->secureDeleteFile($file);
        }

        $this->files = [];
    }

} 