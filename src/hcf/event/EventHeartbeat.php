<?php
/*
* Copyright (C) Sergittos - All Rights Reserved
* Unauthorized copying of this file, via any medium is strictly prohibited
* Proprietary and confidential
*/

declare(strict_types=1);


namespace hcf\event;


use hcf\HCF;
use pocketmine\scheduler\Task;

class EventHeartbeat extends Task {

    public function onRun(): void {
        /*foreach(HCF::getInstance()->getEventManager()->getEvents() as $event) {
            if($event->isEnabled()) {
                $event->decreaseTime();
            }
        }*/
    }

}