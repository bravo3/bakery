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
     *
     * @param $tag
     * @return bool
     */
    public function checkout()
    {
        $this->addLog($this->getPrompt());
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
                    throw new ApplicationException("Git error");
                }
            }

        } while (strpos($this->output, $this->shell->getSmartMarker()) === false);

        $this->output = substr($this->output, 0, -strlen($this->shell->getSmartMarker()));

        if ($this->repo->getTag()) {
            $this->sendCommand('cd "'.$this->repo->getCheckoutPath().'"');
            $this->sendCommand('git checkout "'.$this->repo->getTag().'"');
            $this->sendCommand('cd ~');
        }

        return true;
    }

}
