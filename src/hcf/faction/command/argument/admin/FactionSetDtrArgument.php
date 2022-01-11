<?php

declare(strict_types=1);

namespace hcf\faction\command\argument\admin;

use hcf\command\Argument;
use hcf\faction\FactionFactory;
use hcf\Placeholders;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class FactionSetDtrArgument extends Argument {

    /**
     * @param CommandSender $sender
     * @param string        $commandLabel
     * @param string        $argumentLabel
     * @param array         $args
     */
    public function run(CommandSender $sender, string $commandLabel, string $argumentLabel, array $args): void {
        if (count($args) < 2) {
            $sender->sendMessage(TextFormat::RED . 'Usage: /' . $commandLabel . ' setdtr <faction> <value>');

            return;
        }

        if (($faction = FactionFactory::getInstance()->getFactionName($args[0])) === null) {
            $sender->sendMessage(Placeholders::replacePlaceholders('FACTION_NOT_FOUND', $args[0]));

            return;
        }

        if (!is_numeric($value = $args[1]) || $value > $faction->getMaximumDeathsUntilRaidable()) {
            $sender->sendMessage(Placeholders::replacePlaceholders('INVALID_NUMBER'));

            return;
        }

        $faction->setDeathsUntilRaidable((int) $value);
        $faction->save();

        $sender->sendMessage(Placeholders::replacePlaceholders('FACTION_DTR_UPDATED', $faction->getName(), (string) $value));
    }
}