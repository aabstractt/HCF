<?php

declare(strict_types=1);

namespace hcf\task;

use hcf\session\SessionFactory;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class ScoreboardUpdateTask extends Task {

    /**
     * Actions to execute when run
     */
    public function onRun(): void {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if (!$player->isConnected() || ($session = SessionFactory::getInstance()->getSessionName($player->getName())) === null) {
                continue;
            }

            $session->getScoreboardBuilder()->update();
        }
    }
}