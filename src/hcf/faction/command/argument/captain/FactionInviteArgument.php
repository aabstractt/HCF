<?php

declare(strict_types=1);

namespace hcf\faction\command\argument\captain;

use hcf\api\Argument;
use hcf\faction\FactionFactory;
use hcf\faction\type\FactionRank;
use hcf\HCF;
use hcf\Placeholders;
use hcf\session\SessionFactory;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class FactionInviteArgument extends Argument {

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

        if (count($args) === 0) {
            $sender->sendMessage(TextFormat::RED . 'Usage: /' . $commandLabel . ' invite <player>');

            return;
        }

        $session = SessionFactory::getInstance()->getPlayerSession($sender);

        if (($faction = $session->getFaction()) === null) {
            $sender->sendMessage(Placeholders::replacePlaceholders('COMMAND_FACTION_NOT_IN'));

            return;
        }

        if (!$session->getFactionRank()->isAtLeast(FactionRank::CAPTAIN())) {
            $sender->sendMessage(Placeholders::replacePlaceholders('COMMAND_FACTION_NOT_CAPTAIN'));

            return;
        }

        if ($faction->isRaidable()) {
            $sender->sendMessage(Placeholders::replacePlaceholders('COMMAND_FACTION_RAIDABLE'));

            return;
        }

        if (($targetSession = SessionFactory::getInstance()->getSession($args[0])) === null) {
            $sender->sendMessage(Placeholders::replacePlaceholders('PLAYER_NOT_FOUND', $args[0]));

            return;
        }

        if ($targetSession->getXuid() === $sender->getXuid() && !HCF::isUnderDevelopment()) {
            $sender->sendMessage(Placeholders::replacePlaceholders('YOU_CANT_USE_THIS_ON_YOURSELF'));

            return;
        }

        if ($targetSession->getFaction() !== null && !HCF::isUnderDevelopment()) {
            $sender->sendMessage(Placeholders::replacePlaceholders('PLAYER_IN_FACTION', $targetSession->getName()));

            return;
        }

        if ($faction->isAlreadyInvited($targetSession->getXuid())) {
            $sender->sendMessage(Placeholders::replacePlaceholders('PLAYER_ALREADY_INVITED', $targetSession->getName()));

            return;
        }

        if (count($faction->getMembers()) > FactionFactory::getMaxMembers()) {
            $sender->sendMessage(Placeholders::replacePlaceholders('FACTION_FULL', $faction->getName()));

            return;
        }

        $faction->addInvite($targetSession->getXuid());

        $targetSession->getInstanceNonNull()->sendMessage(Placeholders::replacePlaceholders('FACTION_INVITE_RECEIVED', $faction->getName(), $session->getName()));

        $faction->broadcastMessage(Placeholders::replacePlaceholders('PLAYER_FACTION_INVITE_SENT', $targetSession->getName(), $session->getName()));
    }
}