<?php

declare(strict_types=1);

namespace hcf\faction\command\argument\member;

use hcf\api\Argument;
use hcf\Placeholders;
use hcf\session\SessionFactory;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class FactionDepositArgument extends Argument {

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

        if (count($args) === 0) {
            $sender->sendMessage(TextFormat::RED . sprintf('Usage: /%s %s <amount|all>', $commandLabel, $argumentLabel));

            return;
        }if ($args[0] === 'all') {
            $amount = $session->getBalance();
        } else if (!is_int(($amount = $args[0]))) {
            $sender->sendMessage(Placeholders::replacePlaceholders('INVALID_NUMBER', $args[0]));

            return;
        }

        if ($amount <= 0) {
            $sender->sendMessage(Placeholders::replacePlaceholders('AMOUNT_MUST_BE_POSITIVE'));

            return;
        }

        if ($amount > $session->getBalance()) {
            $sender->sendMessage(Placeholders::replacePlaceholders('NOT_ENOUGH_BALANCE', (string) $amount, (string) $session->getBalance()));

            return;
        }

        $session->decreaseBalance($amount);
        $faction->increaseBalance($amount);

        $session->save();
        $faction->save();

        $faction->broadcastMessage(Placeholders::replacePlaceholders('FACTION_MEMBER_DEPOSITED', $session->getName(), (string) $amount));
    }
}