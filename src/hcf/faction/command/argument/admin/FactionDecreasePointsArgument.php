<?php

declare(strict_types=1);

namespace hcf\faction\command\argument\admin;

use hcf\api\Argument;
use hcf\faction\FactionFactory;
use hcf\Placeholders;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class FactionDecreasePointsArgument extends Argument {

    /**
     * @param CommandSender $sender
     * @param string        $commandLabel
     * @param string        $argumentLabel
     * @param array         $args
     */
    public function run(CommandSender $sender, string $commandLabel, string $argumentLabel, array $args): void {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . 'Run this command in-game');

            return;
        }

        if (count($args) < 2) {
            $sender->sendMessage(TextFormat::RED . 'Usage: /' . $commandLabel . ' decreasepoints <faction> <value>');

            return;
        }

        if (($faction = FactionFactory::getInstance()->getFactionName($args[0])) === null) {
            $sender->sendMessage(Placeholders::replacePlaceholders('FACTION_NOT_FOUND', $args[0]));

            return;
        }

        if (!is_int($value = $args[1])) {
            $sender->sendMessage(Placeholders::replacePlaceholders('INVALID_NUMBER'));

            return;
        }

        $faction->decreasePoints($value);

        $sender->sendMessage(Placeholders::replacePlaceholders('FACTION_POINTS_DECREASED', $faction->getName(), (string) $value));
    }
}