<?php

declare(strict_types=1);

namespace hcf\faction\command\argument\member;

use hcf\api\Argument;
use hcf\Placeholders;
use hcf\session\SessionFactory;
use hcf\TaskUtils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;

class FactionHomeArgument extends Argument {

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

        if ($faction->getHomePosition() === null) {
            $sender->sendMessage(Placeholders::replacePlaceholders('FACTION_HOME_NOT_FOUND'));

            return;
        }

        // TODO: Check timers as combat tag etc stuff

        $session->setHomeTeleport(10);

        TaskUtils::scheduleDelayed(new ClosureTask(function () use ($faction, $sender, $session): void {
            if ($session->getHomeTeleport() === -1) {
                throw new CancelTaskException();
            }

            $sender->teleport($faction->getHomePosition());
        }), 20*10);
    }
}