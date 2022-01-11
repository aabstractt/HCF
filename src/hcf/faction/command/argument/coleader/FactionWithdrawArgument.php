<?php

declare(strict_types=1);

namespace hcf\faction\command\argument\coleader;

use hcf\api\Argument;
use hcf\faction\type\FactionRank;
use hcf\Placeholders;
use hcf\session\SessionFactory;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class FactionWithdrawArgument extends Argument {

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
            $sender->sendMessage(TextFormat::RED . 'Usage: /' . $commandLabel . ' withdraw <amount|all>');

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

        if (is_numeric($args[0])) {
            $amount = intval($args[0]);
        } else if ($args[0] === 'all') {
            $amount = $faction->getBalance();
        }

        if (!isset($amount)) {
            $sender->sendMessage(TextFormat::RED . 'Usage: /' . $commandLabel . ' withdraw <amount|all>');

            return;
        }

        if ($amount <= 0) {
            $sender->sendMessage(Placeholders::replacePlaceholders('INVALID_NUMBER', (string) $amount));

            return;
        }

        if ($amount > $faction->getBalance()) {
            $sender->sendMessage(Placeholders::replacePlaceholders('FACTION_NOT_ENOUGH_BALANCE'));

            return;
        }

        $session->increaseBalance($amount);
        $session->save();

        $faction->decreaseBalance($amount);
        $faction->save();

        $faction->broadcastMessage(Placeholders::replacePlaceholders('PLAYER_WITHDREW_BALANCE', $sender->getName(), (string) $amount));

        $sender->sendMessage(Placeholders::replacePlaceholders('YOU_WITHDRAWN_BALANCE', (string) $amount));
    }
}