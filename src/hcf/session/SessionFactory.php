<?php

declare(strict_types=1);

namespace hcf\session;

use hcf\faction\FactionFactory;
use hcf\faction\type\FactionRank;
use hcf\session\async\LoadSessionAsync;
use hcf\TaskUtils;
use pocketmine\player\Player;
use pocketmine\plugin\PluginException;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;

class SessionFactory {

    use SingletonTrait;

    /** @var Session[] */
    private array $sessions = [];

    /**
     * @param Player $player
     *
     * @return void
     */
    public function loadPlayerSession(Player $player): void {
        TaskUtils::runAsync(new LoadSessionAsync($player->getXuid()), function (LoadSessionAsync $query) use ($player): void {
            if (!is_array($fetch = $query->getResult()) || count($fetch) === 0) {
                $session = new Session($player->getXuid(), $player->getName(), FactionRank::MEMBER());
            } else {
                $session = new Session($player->getXuid(), $player->getName(), FactionRank::valueOf($fetch['rankId']), 0, $fetch['factionRowId']);
            }

            $this->sessions[strtolower($session->getName())] = $session;
        });
    }

    /**
     * @param Player $player
     */
    public function closePlayerSession(Player $player): void {
        if (isset($this->sessions[strtolower($player->getName())])) {
            unset($this->sessions[strtolower($player->getName())]);
        }
    }

    /**
     * @param string $name
     *
     * @return Session|null
     */
    public function getSession(string $name): ?Session {
        if (($player = Server::getInstance()->getPlayerByPrefix($name)) === null) {
            return null;
        }

        return $this->sessions[strtolower($player->getName())] ?? null;
    }

    /**
     * @param string $name
     *
     * @return Session|null
     */
    public function getSessionName(string $name): ?Session {
        return $this->sessions[strtolower($name)] ?? null;
    }

    /**
     * @param Player $player
     *
     * @return Session
     */
    public function getPlayerSession(Player $player): Session {
        return $this->getSessionName($player->getName()) ?? throw new PluginException('Player session is not loaded...');
    }
}