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
use pocketmine\utils\TextFormat;

class StartArgument extends Argument {

    public function __construct() {
        parent::__construct("start", ["enable", "on"]);
    }

    public function run(CommandSender $sender, string $commandLabel, string $argumentLabel, array $args): void {
        $sotw_event = HCF::getInstance()->getEventManager()->getEventById(Event::SOTW);
        if($sotw_event->isEnabled()) {
            $sender->sendMessage(Placeholders::replacePlaceholders("MUST_DISABLE_SOTW"));
            return;
        }
        if(!isset($args[0])) {
            $sender->sendMessage(TextFormat::RED . "Usage: /$commandLabel start {timer (in seconds)}");
            return;
        }
        if(!is_numeric($args[0]) or $args[0] <= 0) {
            $sender->sendMessage(TextFormat::RED . "Please, write a valid number");
            return;
        }
        $sotw_event->setEnabled(true);
        $sotw_event->setTime((int) $args[0]);
        $sender->sendMessage(Placeholders::replacePlaceholders("SOTW_SUCCESSFULLY_ENABLED"));
    }

}