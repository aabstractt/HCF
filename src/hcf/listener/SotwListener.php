<?php
/*
* Copyright (C) Sergittos - All Rights Reserved
* Unauthorized copying of this file, via any medium is strictly prohibited
* Proprietary and confidential
*/

declare(strict_types=1);


namespace hcf\listener;


use hcf\event\Event;
use hcf\HCF;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;

class SotwListener implements Listener {

    public function onReceiveDamage(EntityDamageEvent $event): void {
        if(HCF::getInstance()->getEventManager()->getEventById(Event::SOTW)->isEnabled()) {
            $event->cancel();
        }
    }

}