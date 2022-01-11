<?php

declare(strict_types=1);

namespace hcf\koth\command\argument;

use hcf\command\Argument;
use hcf\koth\KothFactory;
use hcf\Placeholders;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class KothStartArgument extends Argument {

    /**
     * @param CommandSender $sender
     * @param string        $commandLabel
     * @param string        $argumentLabel
     * @param array         $args
     */
    public function run(CommandSender $sender, string $commandLabel, string $argumentLabel, array $args): void {
        if (count($args) === 0) {
            $sender->sendMessage(TextFormat::RED . 'Usage: /' . $commandLabel . ' start <koth_name>');

            return;
        }

        if (KothFactory::getInstance()->getKoth($args[0]) === null) {
            $sender->sendMessage(TextFormat::RED . 'Koth ' . $args[0] . ' not found');

            return;
        }

        KothFactory::getInstance()->setKothName($args[0]);

        $sender->sendMessage(Placeholders::replacePlaceholders('KOTH_SUCCESSFULLY_STARTED'));

        Server::getInstance()->broadcastMessage(Placeholders::replacePlaceholders('KOTH_STARTED', $args[0]));
    }
}