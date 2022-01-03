<?php

declare(strict_types=1);

namespace hcf\listener;

use hcf\session\SessionFactory;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class PlayerJoinListener implements Listener {

    /**
     * @param PlayerJoinEvent $ev
     *
     * @priority NORMAL
     */
    public function onPlayerJoinEvent(PlayerJoinEvent $ev): void {
        SessionFactory::getInstance()->loadPlayerSession($ev->getPlayer());
    }
}