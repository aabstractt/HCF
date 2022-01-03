<?php

declare(strict_types=1);

namespace hcf\listener;

use hcf\session\SessionFactory;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;

class PlayerQuitListener implements Listener {

    /**
     * @param PlayerQuitEvent $ev
     *
     * @priority NORMAL
     */
    public function onPlayerQuitEvent(PlayerQuitEvent $ev): void {
        SessionFactory::getInstance()->closePlayerSession($ev->getPlayer());
    }
}