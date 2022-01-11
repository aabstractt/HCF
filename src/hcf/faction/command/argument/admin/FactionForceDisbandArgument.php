<?php

declare(strict_types=1);

namespace hcf\faction\command\argument\admin;

use hcf\command\Argument;
use hcf\faction\FactionFactory;
use hcf\Placeholders;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class FactionForceDisbandArgument extends Argument {

    /**
     * @param CommandSender $sender
     * @param string        $commandLabel
     * @param string        $argumentLabel
     * @param array         $args
     */
    public function run(CommandSender $sender, string $commandLabel, string $argumentLabel, array $args): void {
        if (count($args) === 0) {
            $sender->sendMessage(TextFormat::RED . 'Usage: /' . $commandLabel . ' forcedisband <faction>');

            return;
        }

        if (($faction = FactionFactory::getInstance()->getFactionName($args[0])) === null) {
            $sender->sendMessage(Placeholders::replacePlaceholders('FACTION_NOT_FOUND', $args[0]));

            return;
        }

        $sender->sendMessage(Placeholders::replacePlaceholders('FACTION_SUCCESSFULLY_DISBAND', $faction->getName()));

        FactionFactory::getInstance()->disbandFaction($faction);
    }
}