<?php

declare(strict_types=1);

namespace hcf\command\defaults;

use hcf\Placeholders;
use hcf\session\SessionFactory;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class TeamLocationCommand extends Command {

    /**
     * @param CommandSender $sender
     * @param string        $commandLabel
     * @param array         $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . 'Run this command in-game');

            return;
        }

        $session = SessionFactory::getInstance()->getPlayerSession($sender);

        if (($faction = $session->getFaction()) === null) {
            $sender->sendMessage(Placeholders::replacePlaceholders('COMMAND_FACTION_NOT_IN'));

            return;
        }

        $faction->broadcastMessage(Placeholders::replacePlaceholders('FACTION_TEAM_LOCATION', $sender->getName(), (string) ($pos = $sender->getPosition())->getFloorX(), (string) $pos->getFloorY(), (string) $pos->getFloorZ()));
    }
}