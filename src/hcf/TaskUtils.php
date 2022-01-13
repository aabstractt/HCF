<?php

declare(strict_types=1);

namespace hcf;

use DaveRandom\CallbackValidator\CallbackType;
use hcf\task\QueryAsyncTask;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class TaskUtils {

    /** @var string */
    protected static string $host;
    /** @var string */
    protected static string $username;
    /** @var string */
    protected static string $password;
    /** @var string */
    protected static string $dbname;
    /** @var int */
    protected static int $port;
    /**
     * @phpstan-var array<string, anyCallable>
     * @phpstan-var array<string, callableVoid>
     * @noinspection PhpUndefinedClassInspection
     */
    private static array $callbacks = [];

    public static function init(): void {
        /** @var array $data */
        $data = HCF::getInstance()->getConfig()->get('mysql');

        $hostSplit = explode(':', $data['host']);

        self::$host = $hostSplit[0];

        self::$username = $data['username'];

        self::$password = $data['password'];

        self::$dbname = $data['dbname'];

        self::$port = (int) ($hostSplit[1] ?? 3306);
    }

    /**
     * @param QueryAsyncTask $query
     * @param callable|null  $subject
     *
     * @phpstan-param anyCallable|callableVoid|null $subject
     * @noinspection PhpUndefinedClassInspection
     */
    public static function runAsync(QueryAsyncTask $query, ?callable $subject = null): void {
        if ($subject !== null) {
            self::$callbacks[spl_object_hash($query)] = $subject;
        }

        if (!isset(self::$host)) {
            self::init();
        }

        $query->host = self::$host;
        $query->user = self::$username;
        $query->password = self::$password;
        $query->database = self::$dbname;
        $query->port = self::$port;

        Server::getInstance()->getAsyncPool()->submitTask($query);
    }

    /**
     * @param QueryAsyncTask $query
     */
    public static function submitAsync(QueryAsyncTask $query): void {
        $callable = self::$callbacks[spl_object_hash($query)] ?? null;

        if (!is_callable($callable)) {
            return;
        }

        if (self::checkCallable(function (QueryAsyncTask $query): void {}, $callable)) {
            $callable($query);
        } else if (self::checkCallable(function (): void {}, $callable)) {
            $callable();
        } else {
            Server::getInstance()->getLogger()->error("Declaration of callable must be compatible with");
        }
    }

    /**
     * @param CallbackType|callable $signature
     * @param callable              $subject
     *
     * @phpstan-param anyCallable|callableVoid|CallbackType $signature
     * @phpstan-param anyCallable $subject
     *
     * @return bool
     * @noinspection PhpUndefinedClassInspection
     */
    private static function checkCallable(CallbackType|callable $signature, callable $subject): bool {
        if(!($signature instanceof CallbackType)){
            $signature = CallbackType::createFromCallable($signature);
        }

        return $signature->isSatisfiedBy($subject);
    }

    /**
     * @param Task $task
     * @param int  $ticks
     */
    public static function scheduleRepeating(Task $task, int $ticks = 20): void {
        HCF::getInstance()->getScheduler()->scheduleRepeatingTask($task, $ticks);
    }

    /**
     * @param Task $task
     * @param int  $delay
     */
    public static function scheduleDelayed(Task $task, int $delay = 20): void {
        HCF::getInstance()->getScheduler()->scheduleDelayedTask($task, $delay);
    }
}