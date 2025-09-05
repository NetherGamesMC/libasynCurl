<?php

declare(strict_types=1);


namespace libasynCurl\thread;


use Closure;
use InvalidArgumentException;
use pocketmine\scheduler\AsyncTask;
use pocketmine\thread\NonThreadSafeValue;
use pocketmine\utils\InternetRequestResult;
use pocketmine\utils\Utils;

abstract class CurlTask extends AsyncTask
{
    /** @var string */
    protected string $page;
    /** @var int */
    protected int $timeout;
    /** @phpstan-var NonThreadSafeValue<array> */
    protected NonThreadSafeValue $headers;

    public function __construct(string $page, int $timeout, array $headers, ?Closure $closure = null)
    {
        $this->page = $page;
        $this->timeout = $timeout;
        $this->headers = new NonThreadSafeValue($headers);

        if ($closure !== null) {
            Utils::validateCallableSignature(function (?InternetRequestResult $result): void {}, $closure);
            $this->storeLocal('closure', $closure);
        }
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return $this->headers->deserialize();
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