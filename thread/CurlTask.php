<?php

declare(strict_types=1);


namespace libasynCurl\thread;


use Closure;
use InvalidArgumentException;
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\InternetRequestResult;
use pocketmine\utils\Utils;
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

        if ($closure !== null) {
            Utils::validateCallableSignature(function(?InternetRequestResult $result) : void{}, $closure);
            $this->storeLocal('closure', $closure);
        }
    }

    public function getHeaders(): array
    {
        return json_decode($this->headers, true, 512, JSON_THROW_ON_ERROR);
    }

    public function onCompletion(): void
    {
        try {
            $closure = $this->fetchLocal('closure');

            if ($closure !== null) {
                $closure($this->getResult());
            }
        } catch (InvalidArgumentException $exception) {

        }
    }
}