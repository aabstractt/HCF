<?php

declare(strict_types=1);

namespace hcf\faction\command\argument\coleader;

use hcf\command\Argument;
use hcf\faction\type\FactionRank;
use hcf\Placeholders;
use hcf\session\SessionFactory;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class FactionKickArgument extends Argument {

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
            $sender->sendMessage(TextFormat::RED . 'Usage: /' . $commandLabel . ' kick <player>');

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

        if ($member->getFactionRank()->isAtLeast($session->getFactionRank())) {
            $sender->sendMessage(Placeholders::replacePlaceholders('YOU_CANNOT_KICK_TARGET', $name));

            return;
        }

        $sender->sendMessage(Placeholders::replacePlaceholders('SUCCESSFULLY_KICKED', $name));

        $faction->broadcastMessage(Placeholders::replacePlaceholders('FACTION_LEFT_FACTION', $name));

        $faction->removeMember($member->getXuid());
    }
}