<?php
/*
* Copyright (C) Sergittos - All Rights Reserved
* Unauthorized copying of this file, via any medium is strictly prohibited
* Proprietary and confidential
*/

declare(strict_types=1);


namespace hcf\event\sotw\command\argument;


use hcf\command\Argument;
use pocketmine\command\CommandSender;

class HelpArgument extends Argument {

    public function __construct() {
        parent::__construct("help");
    }

    public function run(CommandSender $sender, string $commandLabel, string $argumentLabel, array $args): void {
        // TODO
    }

}