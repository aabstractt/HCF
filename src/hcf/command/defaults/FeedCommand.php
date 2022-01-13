<?php

declare(strict_types=1);

namespace hcf\command\defaults;

use hcf\Placeholders;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class FeedCommand extends Command {

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

        if (!$sender->hasPermission('hcf.command.feed')) {
            $sender->sendMessage(TextFormat::RED . 'You don\'t have permissions to use this command.');

            return;
        }

        $sender->getHungerManager()->setFood($sender->getHungerManager()->getMaxFood());

        $sender->sendMessage(Placeholders::replacePlaceholders('HEALED_SUCCESSFULLY'));
    }
}