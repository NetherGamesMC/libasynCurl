<?php

declare(strict_types=1);


namespace libasynCurl\thread;


use Closure;
use pocketmine\utils\Internet;
use function is_array;
use function json_encode;

class CurlPostTask extends CurlTask
{
    /** @var string */
    protected string $args;

    public function __construct(string $page, array|string $args, int $timeout, array $headers, ?Closure $closure = null)
    {
        if (is_array($args)) {
            $this->args = json_encode($args, JSON_THROW_ON_ERROR);
        } else {
            $this->args = $args;
        }

        parent::__construct($page, $timeout, $headers, $closure);
    }

    public function onRun(): void
    {
        $this->setResult(Internet::postURL($this->page, $this->args, $this->timeout, $this->getHeaders()));
    }
}