<?php

declare(strict_types=1);

namespace hcf\faction\type\koth\command\argument;

use hcf\command\Argument;
use hcf\faction\ClaimZone;
use hcf\faction\type\koth\KothFactory;
use hcf\HCF;
use hcf\Placeholders;
use hcf\session\SessionFactory;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class KothCreateArgument extends Argument {

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

        if (count($args) === 0) {
            $sender->sendMessage(TextFormat::RED . 'Usage: /' . $commandLabel . ' create <koth_name>');

            return;
        }

        $session = SessionFactory::getInstance()->getPlayerSession($sender);

        if ($session->getClaimZone() !== null) {
            $sender->sendMessage(Placeholders::replacePlaceholders('ALREADY_REGISTERING_KOTH'));

            return;
        }

        if (KothFactory::getInstance()->getKoth($args[0]) !== null) {
            $sender->sendMessage(Placeholders::replacePlaceholders('KOTH_ALREADY_EXISTS', $args[0]));

            return;
        }

        $sender->sendMessage(Placeholders::replacePlaceholders('PLAYER_START_CLAIMING_KOTH'));

        $claimZone = new ClaimZone(-1, Placeholders::location(HCF::getDefaultWorld()), Placeholders::location(HCF::getDefaultWorld()));
        $claimZone->specify = $args[0];

        $session->setClaimZone($claimZone, 'koth_claiming');
    }
}