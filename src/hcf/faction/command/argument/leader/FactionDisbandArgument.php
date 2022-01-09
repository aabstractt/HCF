<?php

declare(strict_types=1);

namespace hcf\faction\command\argument\leader;

use hcf\api\Argument;
use hcf\faction\FactionFactory;
use hcf\faction\type\FactionRank;
use hcf\Placeholders;
use hcf\session\SessionFactory;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class FactionDisbandArgument extends Argument {

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

        $session = SessionFactory::getInstance()->getPlayerSession($sender);

        if (($faction = $session->getFaction()) === null) {
            $sender->sendMessage(Placeholders::replacePlaceholders('FACTION_PLAYER_NOT_IN_FACTION'));

            return;
        }

        if (!$session->getFactionRank()->isAtLeast(FactionRank::LEADER())) {
            $sender->sendMessage(Placeholders::replacePlaceholders('COMMAND_FACTION_NOT_LEADER'));

            return;
        }

        FactionFactory::getInstance()->disbandFaction($faction);

        Server::getInstance()->broadcastMessage(Placeholders::replacePlaceholders('FACTION_DISBANDED', $sender->getName(), $faction->getName()));
    }
}