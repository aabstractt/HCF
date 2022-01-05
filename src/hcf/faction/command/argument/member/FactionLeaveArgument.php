<?php

declare(strict_types=1);

namespace hcf\faction\command\argument\member;

use hcf\api\Argument;
use hcf\faction\FactionFactory;
use hcf\faction\type\FactionRank;
use hcf\Placeholders;
use hcf\session\SessionFactory;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class FactionLeaveArgument extends Argument {

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

        if ($session->getFactionRank() === FactionRank::LEADER()) {
            $sender->sendMessage(Placeholders::replacePlaceholders('YOU_CANNOT_LEAVE_FACTION_LEAD'));

            return;
        }

        if (($targetFaction = FactionFactory::getInstance()->getFactionAt($sender->getPosition())) !== null && $targetFaction->getRowId() === $faction->getRowId()) {
            $sender->sendMessage(Placeholders::replacePlaceholders('MUST_LEAVE_FACTION_TERRITORY'));

            return;
        }


        $sender->sendMessage(Placeholders::replacePlaceholders('PLAYER_FACTION_LEFT'));
        $faction->broadcastMessage(Placeholders::replacePlaceholders('FACTION_PLAYER_LEFT', $sender->getName()));

        $faction->removeMember($sender->getXuid());
        $faction->save();

        $session->setFaction(null);
        $session->setFactionRank(FactionRank::MEMBER());
        $session->save();
    }
}