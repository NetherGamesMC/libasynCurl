<?php

namespace libasynCurl\thread;

use pocketmine\utils\Internet;

class CurlHeadTask extends CurlTask
{
    public function onRun(): void
    {
        $this->setResult(Internet::simpleCurl($this->page, $this->timeout, $this->getHeaders(), [
            CURLOPT_CUSTOMREQUEST => 'HEAD',
            CURLOPT_NOBODY => true
        ]));
    }
}