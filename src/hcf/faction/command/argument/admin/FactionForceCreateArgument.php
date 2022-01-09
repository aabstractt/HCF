<?php

declare(strict_types=1);

namespace hcf\faction\command\argument\admin;

use hcf\api\Argument;
use hcf\faction\async\SaveFactionAsync;
use hcf\faction\FactionFactory;
use hcf\faction\type\FactionRank;
use hcf\HCF;
use hcf\Placeholders;
use hcf\session\SessionFactory;
use hcf\TaskUtils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class FactionForceCreateArgument extends Argument {

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

        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::RED . 'Usage: /' . $commandLabel . ' forcecreate <spawn|road|koth>');

            return;
        }

        $session = SessionFactory::getInstance()->getPlayerSession($sender);

        if ($session->getFaction() !== null) {
            $sender->sendMessage(Placeholders::replacePlaceholders('COMMAND_FACTION_ATTEMPT_CREATE'));

            return;
        }

        if (FactionFactory::getInstance()->getServerFaction($args[0]) !== null) {
            $sender->sendMessage(Placeholders::replacePlaceholders('FACTION_ALREADY_EXISTS', $args[0]));

            return;
        }

        TaskUtils::runAsync(new SaveFactionAsync(serialize([$args[0], 0.1])), function (SaveFactionAsync $query) use ($args, $session, $sender): void {
            if (!is_int($rowId = $query->getResult())) {
                $sender->sendMessage(TextFormat::RED . 'An error was occurred...');

                return;
            }

            FactionFactory::getInstance()->joinFaction($session, FactionFactory::checkFaction($rowId, $args[0]), FactionRank::LEADER());

            $session->setLastFactionEdit(HCF::dateNow());
        });
    }
}