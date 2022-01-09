<?php

declare(strict_types=1);

namespace hcf\faction\command\argument\coleader;

use hcf\api\Argument;
use hcf\faction\type\FactionRank;
use hcf\Placeholders;
use hcf\session\SessionFactory;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class FactionClaimArgument extends Argument {

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

        if (!$session->getFactionRank()->isAtLeast(FactionRank::COLEADER())) {
            $sender->sendMessage(Placeholders::replacePlaceholders('COMMAND_FACTION_NOT_COLEADER'));

            return;
        }

        if ($faction->getClaimZone() !== null) {
            $sender->sendMessage(Placeholders::replacePlaceholders('FACTION_ALREADY_HAVE_CLAIM'));

            return;
        }

        $sender->sendMessage(Placeholders::replacePlaceholders('PLAYER_FACTION_CLAIMING'));
    }
}