<?php

declare(strict_types=1);

namespace hcf\koth\command\argument;

use hcf\command\Argument;
use hcf\faction\ClaimZone;
use hcf\koth\KothFactory;
use hcf\Placeholders;
use pocketmine\command\CommandSender;

class KothListArgument extends Argument {

    /**
     * @param CommandSender $sender
     * @param string        $commandLabel
     * @param string        $argumentLabel
     * @param array         $args
     */
    public function run(CommandSender $sender, string $commandLabel, string $argumentLabel, array $args): void {
        $sender->sendMessage(Placeholders::replacePlaceholders('COMMAND_KOTH_LIST', implode("\n", array_map(function (string $kothName, ClaimZone $claimZone): string {
            return Placeholders::replacePlaceholders('COMMAND_KOTH_LIST_FORMAT',
                $kothName,
                (string) ($firstCorner = $claimZone->getFirsCorner())->getFloorX(),
                (string) $firstCorner->getFloorY(),
                (string) $firstCorner->getFloorZ(),
                $firstCorner->getWorld()->getFolderName(),
                KothFactory::getInstance()->getKothName() === $kothName ? '&aRunning' : '&cIdle'
            );
        }, array_keys($koths = KothFactory::getInstance()->getKoths()), $koths))));
    }
}