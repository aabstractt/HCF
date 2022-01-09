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

class SotwCommand extends Command {

    public function __construct() {
        parent::__construct("sotw", "Sotw commands");

        $this->addArgument(
            new HelpArgument(),
            new StartArgument("start", ["enable", "on"]),
            new StopArgument("stop", ["disable", "off"])
        );
    }
}