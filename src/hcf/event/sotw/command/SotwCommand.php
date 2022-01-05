<?php
/*
* Copyright (C) Sergittos - All Rights Reserved
* Unauthorized copying of this file, via any medium is strictly prohibited
* Proprietary and confidential
*/

declare(strict_types=1);


namespace hcf\event\sotw\command;


use hcf\api\Command;
use hcf\event\sotw\command\argument\HelpArgument;
use hcf\event\sotw\command\argument\StartArgument;
use hcf\event\sotw\command\argument\StopArgument;
use pocketmine\command\CommandSender;

class SotwCommand extends Command {

    public function __construct() {
        $this->setPermission("command.sotw");
        $this->addArgument(
            new HelpArgument(),
            new StartArgument(),
            new StopArgument()
        );
        parent::__construct("sotw", "Sotw commands");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if($this->testPermission($sender)) {
            parent::execute($sender, $commandLabel, $args);
        }
    }

}