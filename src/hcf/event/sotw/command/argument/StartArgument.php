<?php
/*
* Copyright (C) Sergittos - All Rights Reserved
* Unauthorized copying of this file, via any medium is strictly prohibited
* Proprietary and confidential
*/

declare(strict_types=1);

namespace hcf\event\sotw\command\argument;

use hcf\command\Argument;
use hcf\event\Event;
use hcf\event\EventManager;
use hcf\Placeholders;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class StartArgument extends Argument {

    /**
     * @param CommandSender $sender
     * @param string        $commandLabel
     * @param string        $argumentLabel
     * @param array         $args
     *
     * @return void
     */
    public function run(CommandSender $sender, string $commandLabel, string $argumentLabel, array $args): void {
        if (($event = EventManager::getInstance()->getEventById(Event::SOTW)) === null) {
            return;
        }

        if($event->isEnabled()) {
            $sender->sendMessage(Placeholders::replacePlaceholders("MUST_DISABLE_SOTW"));

            return;
        }

        if(!isset($args[0])) {
            $sender->sendMessage(TextFormat::RED . "Usage: /" . $commandLabel . " start {timer (in seconds)}");

            return;
        }

        if(!is_numeric($args[0]) or $args[0] <= 0) {
            $sender->sendMessage(TextFormat::RED . "Please, write a valid number");

            return;
        }

        $event->setEnabled(true);
        $event->setTime((int) $args[0]);
        $sender->sendMessage(Placeholders::replacePlaceholders("SOTW_SUCCESSFULLY_ENABLED"));
    }
}