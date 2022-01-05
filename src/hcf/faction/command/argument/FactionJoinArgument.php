<?php

declare(strict_types=1);

namespace hcf\faction\command\argument;

use hcf\api\Argument;
use hcf\faction\FactionFactory;
use hcf\HCF;
use hcf\Placeholders;
use hcf\session\SessionFactory;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class FactionJoinArgument extends Argument {

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

        if (count($args) === 0) {
            $sender->sendMessage(TextFormat::RED . sprintf('Usage: /%s %s <faction_name>', $commandLabel, $argumentLabel));

            return;
        }

        $session = SessionFactory::getInstance()->getPlayerSession($sender);

        if ($session->getFaction() !== null && !HCF::isUnderDevelopment()) {
            $sender->sendMessage(Placeholders::replacePlaceholders('COMMAND_FACTION_ATTEMPT_JOIN'));

            return;
        }

        if (($faction = FactionFactory::getInstance()->getFactionName($args[0])) === null || (!$faction->isOpen() && !$faction->isAlreadyInvited($sender->getXuid()))) {
            $sender->sendMessage(Placeholders::replacePlaceholders('FACTION_NOT_INVITED', $args[0]));

            return;
        }

        if (!$faction->isOpen() && $faction->getRegenStatus() === FactionFactory::STATUS_PAUSED) {
            $sender->sendMessage(Placeholders::replacePlaceholders('PLAYER_ATTEMPT_JOIN_ON_FREEZE'));

            return;
        }

        if ($faction->isAlreadyInvited($sender->getXuid())) {
            $faction->removeInvite($sender->getXuid());
        }

        if (count($faction->getMembers()) > FactionFactory::getMaxMembers()) {
            $sender->sendMessage(Placeholders::replacePlaceholders('FACTION_FULL', $faction->getName()));

            return;
        }

        FactionFactory::getInstance()->joinFaction($session, $faction);
    }
}