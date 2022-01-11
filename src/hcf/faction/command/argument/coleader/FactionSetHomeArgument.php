<?php

declare(strict_types=1);

namespace hcf\faction\command\argument\coleader;

use hcf\command\Argument;
use hcf\faction\async\SaveHomeAsync;
use hcf\faction\FactionFactory;
use hcf\faction\type\FactionRank;
use hcf\Placeholders;
use hcf\session\SessionFactory;
use hcf\task\QueryAsyncTask;
use hcf\TaskUtils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class FactionSetHomeArgument extends Argument {

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

        if (($targetFaction = FactionFactory::getInstance()->getFactionAt(($loc = $sender->getLocation()))) !== null && $targetFaction->getRowId() !== $faction->getRowId()) {
            $sender->sendMessage(Placeholders::replacePlaceholders('YOU_CANNOT_DO_THIS_HERE'));

            return;
        }

        TaskUtils::runAsync(new SaveHomeAsync($faction->getRowId(), Placeholders::locationToString($loc), $faction->getHomePosition() === null), function (QueryAsyncTask $query) use ($faction, $loc, $sender): void {
            $faction->setHomePosition($loc);

            $faction->broadcastMessage(Placeholders::replacePlaceholders('FACTION_HOME_CHANGED', $sender->getName()));
        });
    }
}