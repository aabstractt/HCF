<?php

declare(strict_types=1);

namespace hcf\faction\command\argument\admin;

use hcf\api\Argument;
use hcf\faction\async\DeleteClaimAsync;
use hcf\faction\FactionFactory;
use hcf\Placeholders;
use hcf\TaskUtils;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class FactionForceUnclaimArgument extends Argument {

    /**
     * @param CommandSender $sender
     * @param string        $commandLabel
     * @param string        $argumentLabel
     * @param array         $args
     */
    public function run(CommandSender $sender, string $commandLabel, string $argumentLabel, array $args): void {
        if (count($args) === 0) {
            $sender->sendMessage(TextFormat::RED . 'Usage: /' . $commandLabel . ' forcedisband <faction>');

            return;
        }

        if (($faction = FactionFactory::getInstance()->getFactionName($args[0])) === null) {
            $sender->sendMessage(Placeholders::replacePlaceholders('FACTION_NOT_FOUND', $args[0]));

            return;
        }

        if ($faction->getClaimZone() === null) {
            $sender->sendMessage(Placeholders::replacePlaceholders('FACTION_CLAIM_NOT_FOUND', $faction->getName()));

            return;
        }

        $faction->setClaimZone(null);

        TaskUtils::runAsync(new DeleteClaimAsync($faction->getRowId()));

        $sender->sendMessage(Placeholders::replacePlaceholders('FACTION_CLAIM_SUCCESSFULLY_REMOVED', $faction->getName()));
    }
}