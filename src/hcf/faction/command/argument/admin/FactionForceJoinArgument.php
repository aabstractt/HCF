<?php

declare(strict_types=1);

namespace hcf\faction\command\argument\admin;

use hcf\command\Argument;
use hcf\faction\FactionFactory;
use hcf\Placeholders;
use hcf\session\SessionFactory;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class FactionForceJoinArgument extends Argument {

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

        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::RED . 'Usage: /' . $commandLabel . ' forcejoin <factionName>');

            return;
        }

        $session = SessionFactory::getInstance()->getPlayerSession($sender);

        if ($session->getFaction() !== null) {
            $sender->sendMessage(Placeholders::replacePlaceholders('COMMAND_FACTION_ATTEMPT_JOIN'));

            return;
        }

        if (($faction = FactionFactory::getInstance()->getFactionName($args[0])) === null) {
            $sender->sendMessage(Placeholders::replacePlaceholders('FACTION_NOT_INVITED', $args[0]));

            return;
        }

        FactionFactory::getInstance()->joinFaction($session, $faction);
    }
}