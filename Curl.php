<?php

declare(strict_types=1);


namespace libasynCurl;


use Closure;
use InvalidArgumentException;
use libasynCurl\thread\CurlDeleteTask;
use libasynCurl\thread\CurlGetTask;
use libasynCurl\thread\CurlPostTask;
use libasynCurl\thread\CurlPutTask;
use libasynCurl\thread\CurlThreadPool;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;

class Curl
{
    /** @var bool */
    private static bool $registered = false;
    /** @var CurlThreadPool */
    private static CurlThreadPool $threadPool;

    public static function register(PluginBase $plugin): void
    {
        if (self::isRegistered()) {
            throw new InvalidArgumentException("{$plugin->getName()} attempted to register " . self::class . " twice.");
        }

        $server = $plugin->getServer();
        self::$threadPool = new CurlThreadPool(CurlThreadPool::POOL_SIZE, CurlThreadPool::MEMORY_LIMIT, $server->getLoader(), $server->getLogger(), $server->getTickSleeper());

        $plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            self::$threadPool->collectTasks();
        }), CurlThreadPool::COLLECT_INTERVAL);
        $plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            self::$threadPool->triggerGarbageCollector();
        }), CurlThreadPool::GARBAGE_COLLECT_INTERVAL);

        self::$registered = true;
    }

    public static function isRegistered(): bool
    {
        return self::$registered;
    }

    public static function postRequest(string $page, array|string $args, int $timeout = 10, array $headers = [], Closure $closure = null): void
    {
        self::$threadPool->submitTask(new CurlPostTask($page, $args, $timeout, $headers, $closure));
    }

    public static function putRequest(string $page, array|string $args, int $timeout = 10, array $headers = [], Closure $closure = null): void
    {
        self::$threadPool->submitTask(new CurlPutTask($page, $args, $timeout, $headers, $closure));
    }

    public static function deleteRequest(string $page, array|string $args, int $timeout = 10, array $headers = [], Closure $closure = null): void
    {
        self::$threadPool->submitTask(new CurlDeleteTask($page, $args, $timeout, $headers, $closure));
    }

    public static function getRequest(string $page, int $timeout = 10, array $headers = [], Closure $closure = null): void
    {
        self::$threadPool->submitTask(new CurlGetTask($page, $timeout, $headers, $closure));
    }
}