<?php

declare(strict_types=1);

namespace hcf\listener;

use hcf\session\SessionFactory;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;

class PlayerMoveListener implements Listener {

    /**
     * @param PlayerMoveEvent $ev
     *
     * @priority NORMAL
     */
    public function onPlayerMoveEvent(PlayerMoveEvent $ev): void {
        $player = $ev->getPlayer();

        $session = SessionFactory::getInstance()->getSessionName($player->getName());

        if ($session === null) {
            return;
        }

        $from = $ev->getFrom();
        $to = $ev->getTo();

        if ($to->x !== $from->x || $to->y !== $from->y || $to->z !== $from->z) {
            $session->setHomeTeleport(-1);
        }
    }
}