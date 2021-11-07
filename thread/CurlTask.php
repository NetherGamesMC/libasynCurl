<?php

declare(strict_types=1);


namespace libasynCurl\thread;


use Closure;
use pocketmine\scheduler\AsyncTask;
use function json_decode;
use function json_encode;

abstract class CurlTask extends AsyncTask
{
    /** @var string */
    protected string $page;
    /** @var int */
    protected int $timeout;
    /** @var string */
    protected string $headers;

    public function __construct(string $page, int $timeout, array $headers, Closure $closure = null)
    {
        $this->page = $page;
        $this->timeout = $timeout;
        $this->headers = json_encode($headers, JSON_THROW_ON_ERROR);

        $this->storeLocal('closure', $closure);
    }

    public function getHeaders(): array
    {
        return json_decode($this->headers, true, 512, JSON_THROW_ON_ERROR);
    }

    public function onCompletion(): void
    {
        $closure = $this->fetchLocal('closure');

        if ($closure !== null) {
            $closure($this->getResult());
        }
    }
}