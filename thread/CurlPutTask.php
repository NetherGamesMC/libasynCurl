<?php

namespace libasynCurl\thread;

use Closure;
use pocketmine\utils\Internet;
use pocketmine\utils\InternetException;
use pocketmine\utils\InternetRequestResult;

class CurlPutTask extends CurlTask
{
    /** @var string */
    protected string $args;

    public function __construct(string $page, array|string $args, int $timeout, array $headers, Closure $closure = null)
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
        $this->setResult(self::putURL($this->page, $this->args, $this->timeout, $this->getHeaders()));
    }

    /**
     * PUTs data to a URL
     * NOTE: This is a blocking operation and can take a significant amount of time. It is inadvisable to use this method on the main thread.
     *
     * @param string|string[] $args
     * @param string[] $extraHeaders
     * @param string|null $err reference parameter, will be set to the output of curl_error(). Use this to retrieve errors that occurred during the operation.
     * @phpstan-param string|array<string, string> $args
     * @phpstan-param list<string> $extraHeaders
     */
    public static function putURL(string $page, array|string $args, int $timeout = 10, array $extraHeaders = [], &$err = null): ?InternetRequestResult
    {
        try {
            return Internet::simpleCurl($page, $timeout, $extraHeaders, [
                CURLOPT_POST => 1,
                CURLOPT_CUSTOMREQUEST => "PUT",
                CURLOPT_POSTFIELDS => http_build_query($args)
            ]);
        } catch (InternetException $ex) {
            $err = $ex->getMessage();
            return null;
        }
    }
}