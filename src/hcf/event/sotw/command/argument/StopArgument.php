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
use hcf\HCF;
use hcf\Placeholders;
use pocketmine\command\CommandSender;

class StopArgument extends Argument {

    public function __construct() {
        parent::__construct("stop", ["disable", "off"]);
    }

    public function run(CommandSender $sender, string $commandLabel, string $argumentLabel, array $args): void {
        $sotw_event = HCF::getInstance()->getEventManager()->getEventById(Event::SOTW);
        if(!$sotw_event->isEnabled()) {
            $sender->sendMessage(Placeholders::replacePlaceholders("MUST_ENABLE_SOTW"));
            return;
        }
        $sotw_event->setEnabled(false);
        $sotw_event->setTime(0);
        $sender->sendMessage(Placeholders::replacePlaceholders("SOTW_SUCCESSFULLY_DISABLED"));
    }

}