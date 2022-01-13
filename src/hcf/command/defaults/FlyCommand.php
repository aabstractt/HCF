<?php

declare(strict_types=1);

namespace hcf\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class FlyCommand extends Command {

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

        if (!$sender->hasPermission('hcf.command.fly')) {
            $sender->sendMessage(TextFormat::RED . 'You don\'t have permissions to use this command.');

            return;
        }

        $sender->setAllowFlight(!$sender->getAllowFlight());
        $sender->setFlying($sender->getAllowFlight());

        $sender->sendMessage(TextFormat::GREEN . 'Flying mode has been ' . ($sender->getAllowFlight() ? 'enabled' : 'disabled'));
    }
}