<?php
namespace Bravo3\Bakery\Services;

class GitCloner extends AbstractCloner implements RepositoryCloner
{
    const FAILSAFE_TIMEOUT = 10;

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

        $timeout = 0;
        do {
            $out = $this->shell->readUntilPause(0.5, true);
            $this->addLog($out);

            if ($out) {
                // New content
                $timeout = 0;

                // Check for username/password request
                if (preg_match('/Username.*: $/', $this->output)) {
                    $this->shell->sendln($this->repo->getUsername());
                }
                if (preg_match('/Password.*: $/', $this->output)) {
                    $this->shell->sendln($this->repo->getPassword());
                }

            } else {
                // Nothing new
                $timeout += 0.5;

                if ($timeout >= self::FAILSAFE_TIMEOUT) {
                    $this->addLog('-- FAILSAFE TIMEOUT --');
                    return false;
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
