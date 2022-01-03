<?php

declare(strict_types=1);

namespace hcf\task;

use Exception;
use hcf\TaskUtils;
use ReflectionClass;
use hcf\utils\MySQL;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

abstract class QueryAsyncTask extends AsyncTask {

    /** @var string */
    public string $host;
    /** @var string */
    public string $user;
    /** @var string */
    public string $password;
    /** @var string */
    public string $database;
    /** @var int */
    public int $port;
    /** @var Exception|null */
    private ?Exception $logException = null;

    /**
     * @param MySQL $mysqli
     */
    abstract public function query(MySQL $mysqli): void;

    final public function onRun(): void {
        try {
            $this->query($mysqli = new MySQL($this->host, $this->user, $this->password, $this->database, $this->port));

            $mysqli->close();
        } catch (Exception $e) {
            $this->logException = $e;
        }
    }

    public function onCompletion(): void {
        if ($this->logException !== null) {
            Server::getInstance()->getLogger()->critical("Could not execute asynchronous task " . (new ReflectionClass($this))->getShortName() . ": Task crashed");

            Server::getInstance()->getLogger()->logException($this->logException);

            return;
        }

        TaskUtils::submitAsync($this);
    }
}