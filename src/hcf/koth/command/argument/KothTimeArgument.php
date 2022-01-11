<?php

declare(strict_types=1);

namespace hcf\koth\command\argument;

use hcf\api\Argument;
use hcf\koth\KothFactory;
use hcf\Placeholders;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class KothTimeArgument extends Argument {

    /**
     * @param CommandSender $sender
     * @param string        $commandLabel
     * @param string        $argumentLabel
     * @param array         $args
     */
    public function run(CommandSender $sender, string $commandLabel, string $argumentLabel, array $args): void {
        if (count($args) < 2) {
            $sender->sendMessage(TextFormat::RED . 'Usage: /' . $commandLabel . ' time <koth_name> <time>');

            return;
        }

        if (KothFactory::getInstance()->getKoth($args[0]) === null) {
            $sender->sendMessage(TextFormat::RED . 'Koth ' . $args[0] . ' not found');

            return;
        }

        if (!is_numeric($args[1])) {
            $sender->sendMessage(Placeholders::replacePlaceholders('INVALID_NUMBER', $args[1]));

            return;
        }

        KothFactory::getInstance()->setKothTime($args[0], (int) $args[1]);

        KothFactory::getInstance()->saveKoths($args[0], ['time' => (int) $args[1]]);

        $sender->sendMessage(Placeholders::replacePlaceholders('KOTH_TIME_UPDATED', $args[0], (string) $args[1]));
    }
}