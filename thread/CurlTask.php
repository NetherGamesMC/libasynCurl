<?php

declare(strict_types=1);


namespace libasynCurl\thread;


use Closure;
use InvalidArgumentException;
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\InternetRequestResult;
use pocketmine\utils\Utils;
use function igbinary_serialize;
use function igbinary_unserialize;

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

        $serialized_headers = igbinary_serialize($headers);
        if ($serialized_headers === null) {
            throw new InvalidArgumentException("Headers cannot be serialized");
        }
        $this->headers = $serialized_headers;

        if ($closure !== null) {
            Utils::validateCallableSignature(function (?InternetRequestResult $result): void {}, $closure);
            $this->storeLocal('closure', $closure);
        }
    }

    public function getHeaders(): array
    {
        /** @var array $headers */
        $headers = igbinary_unserialize($this->headers);

        return $headers;
    }

    public function onCompletion(): void
    {
        try {
            /** @var Closure $closure */
            $closure = $this->fetchLocal('closure');
        } catch (InvalidArgumentException $exception) {
            return;
        }

        if ($closure !== null) {
            $closure($this->getResult());
        }
    }
}