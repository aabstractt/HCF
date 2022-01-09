<?php
/*
* Copyright (C) Sergittos - All Rights Reserved
* Unauthorized copying of this file, via any medium is strictly prohibited
* Proprietary and confidential
*/

declare(strict_types=1);

namespace hcf\event\sotw\command\argument;

use hcf\api\Argument;
use hcf\event\Event;
use hcf\event\EventManager;
use hcf\Placeholders;
use pocketmine\command\CommandSender;

class StopArgument extends Argument {

    public function run(CommandSender $sender, string $commandLabel, string $argumentLabel, array $args): void {
        if (($event = EventManager::getInstance()->getEventById(Event::SOTW)) === null) {
            return;
        }

        if(!$event->isEnabled()) {
            $sender->sendMessage(Placeholders::replacePlaceholders("MUST_ENABLE_SOTW"));

            return;
        }

        $event->setEnabled(false);
        $event->setTime(0);
        $sender->sendMessage(Placeholders::replacePlaceholders("SOTW_SUCCESSFULLY_DISABLED"));
    }
}