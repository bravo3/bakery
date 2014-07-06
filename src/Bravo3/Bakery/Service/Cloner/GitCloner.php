<?php
namespace Bravo3\Bakery\Service\Cloner;

use Bravo3\Bakery\Exception\ApplicationException;
use Bravo3\Bakery\Exception\SecurityException;

class GitCloner extends AbstractCloner implements RepositoryCloner
{
    const FAILSAFE_TIMEOUT  = 10;
    const FINGERPRINT_ERROR = "Fingerprint mismatch - possible man in the middle attack!";
    const FINGERPRINT_REGEX = '/([A-Z]{3}) key fingerprint is (.+)\./';
    const NOT_FOUND_REGEX   = '/git: command not found/';
    const NOT_FOUND_ERROR   = "Git not installed on remote";
    const PERMISSION_ERROR  = "Permission denied";
    const PERMISSION_REGEX  = '/Permission denied \((.*)\)\./';
    const FATAL_REGEX       = '/^fatal: (.*)$/m';
    const FATAL_ERROR       = 'Git fatal error';

    /**
     * Checkout a tag/branch
     */
    public function checkout()
    {
        // Path to the .git directory - if this exists then we don't need to do a clone first
        $git = $this->repo->getCheckoutPath();
        if (substr($git, -1) != DIRECTORY_SEPARATOR) {
            $git .= DIRECTORY_SEPARATOR.'.git';
        } else {
            $git .= '.git';
        }

        // Test if the directory exists
        $exists = $this->shell->sendSmartCommand('test -d "'.$git.'" && echo "EXISTS"', true, 5, true) == 'EXISTS';

        if (!$exists) {
            // Git repo not found, clone it
            $this->shell->sendln('git clone "'.$this->repo->getUri().'" "'.$this->repo->getCheckoutPath().'"');

            $checked_fingerprint = false;
            $timeout             = 0;
            do {
                $out = $this->shell->readUntilPause(0.5, true);
                $this->addLog($out);

                if ($out) {
                    // New content
                    $timeout = 0;

                    // Check for a fingerprint check
                    if (!$checked_fingerprint) {
                        $matches = null;
                        // Get the fingerprint to check it -
                        if (preg_match(self::FINGERPRINT_REGEX, $this->output, $matches)) {
                            $fingerprint = $matches[2];

                            if ($this->repo->getHostFingerprint()) {
                                // Fingerprint was provided, check it
                                if ($fingerprint != $this->repo->getHostFingerprint()) {
                                    $this->shell->sendln('no');
                                    throw new SecurityException(self::FINGERPRINT_ERROR);
                                }
                            }
                            // All good or don't care - yes we want to continue
                            $this->shell->sendln('yes');
                            $checked_fingerprint = true;
                        }
                    }

                    // Check that git didn't fail on account of not being installed
                    if (preg_match(self::NOT_FOUND_REGEX, $this->output)) {
                        throw new ApplicationException(self::NOT_FOUND_ERROR);
                    }

                    // Check for bad permissions
                    $matches = null;
                    if (preg_match(self::PERMISSION_REGEX, $this->output, $matches)) {
                        throw new SecurityException(self::PERMISSION_ERROR.' ('.$matches[1].')');
                    }

                    // Check for general failure
                    $matches = null;
                    if (preg_match(self::FATAL_REGEX, $this->output, $matches)) {
                        throw new ApplicationException(self::FATAL_ERROR.' ('.$matches[1].')');
                    }

                } else {
                    // Nothing new
                    $timeout += 0.5;

                    if ($timeout >= self::FAILSAFE_TIMEOUT) {
                        throw new ApplicationException("Git timeout");
                    }
                }

            } while (strpos($this->output, $this->shell->getSmartMarker()) === false);

            $this->output = substr($this->output, 0, -strlen($this->shell->getSmartMarker()));
        }

        // Checkout the correct tag
        $wd = $this->shell->sendSmartCommand('pwd', true, 5, true);
        $this->sendCommand('cd "'.$this->repo->getCheckoutPath().'"');

        $this->sendCommand('git fetch --all');
        if ($this->repo->getTag()) {
            $this->sendCommand('git reset --hard "'.$this->repo->getTag().'"');
        }

        if ($wd) {
            $this->sendCommand('cd "'.$wd.'"');
        } else {
            $this->sendCommand('cd ~');
        }


    }


}
