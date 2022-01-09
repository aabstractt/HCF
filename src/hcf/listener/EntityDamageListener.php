<?php

declare(strict_types=1);

namespace hcf\listener;

use hcf\event\Event;
use hcf\event\EventManager;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;

class EntityDamageListener implements Listener {

    /**
     * @param EntityDamageEvent $ev
     *
     * @priority NORMAL
     */
    public function onEntityDamageEvent(EntityDamageEvent $ev): void {
        if (($event = EventManager::getInstance()->getEventById(Event::SOTW)) === null) {
            return;
        }

        if ($event->isEnabled()) {
            $ev->cancel();
        }
    }
}