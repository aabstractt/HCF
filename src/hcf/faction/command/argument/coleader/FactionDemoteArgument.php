<?php

declare(strict_types=1);

namespace hcf\faction\command\argument\coleader;

use hcf\api\Argument;
use hcf\faction\type\FactionRank;
use hcf\Placeholders;
use hcf\session\async\SaveSessionAsync;
use hcf\session\SessionFactory;
use hcf\TaskUtils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class FactionDemoteArgument extends Argument {

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
            $sender->sendMessage(Placeholders::replacePlaceholders('COMMAND_FACTION_NOT_IN'));

            return;
        }

        if (!$session->getFactionRank()->isAtLeast(FactionRank::COLEADER())) {
            $sender->sendMessage(Placeholders::replacePlaceholders('COMMAND_FACTION_NOT_COLEADER'));

            return;
        }

        if (count($args) === 0) {
            $sender->sendMessage(TextFormat::RED . 'Usage: /' . $commandLabel . ' demote <player>');

            return;
        }

        $name = $args[0];

        if (($target = Server::getInstance()->getPlayerByPrefix($name)) !== null) {
            $name = $target->getName();
        }

        if (strtolower($name) === strtolower($sender->getName())) {
            $sender->sendMessage(Placeholders::replacePlaceholders('YOU_CANT_USE_THIS_ON_YOURSELF'));

            return;
        }

        if (($member = $faction->getMember($name)) === null) {
            $sender->sendMessage(Placeholders::replacePlaceholders('FACTION_TARGET_NOT_IN_FACTION', $name));

            return;
        }

        if ($member->getFactionRank()->isAtLeast($session->getFactionRank()) || $member->getFactionRank()->ordinal() === FactionRank::MEMBER()->ordinal()) {
            $sender->sendMessage(Placeholders::replacePlaceholders('YOU_CANNOT_DEMOTE_TARGET', $name));

            return;
        }

        $member->setFactionRank($factionRank = $member->getFactionRank() === FactionRank::COLEADER() ? FactionRank::CAPTAIN() : FactionRank::MEMBER());

        $faction->broadcastMessage(Placeholders::replacePlaceholders('FACTION_PLAYER_DEMOTED', $factionRank->getStars(), $member->getName(), $factionRank->name()));

        if (($targetSession = SessionFactory::getInstance()->getSessionName($member->getName())) !== null) {
            $targetSession->setFactionRank($factionRank);

            $targetSession->save();

            return;
        }

        TaskUtils::runAsync(new SaveSessionAsync($member->getXuid(), $member->getName(), $faction->getRowId(), $factionRank->ordinal(), 0, -1));
    }
}