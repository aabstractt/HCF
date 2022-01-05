<?php

declare(strict_types=1);

namespace hcf\faction\command\argument\member;

use hcf\api\Argument;
use pocketmine\command\CommandSender;

class FactionHomeArgument extends Argument {

    /**
     * @param CommandSender $sender
     * @param string        $commandLabel
     * @param string        $argumentLabel
     * @param array         $args
     */
    public function run(CommandSender $sender, string $commandLabel, string $argumentLabel, array $args): void {
    }
}