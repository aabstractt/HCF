<?php

declare(strict_types=1);

namespace hcf\faction\command;

use hcf\api\Command;
use hcf\faction\command\argument\member\FactionLeaveArgument;
use hcf\faction\command\argument\player\FactionCreateArgument;
use hcf\faction\command\argument\player\FactionWhoArgument;
use pocketmine\lang\Translatable;

class FactionCommand extends Command {

    /**
     * @param string                   $name
     * @param Translatable|string      $description
     * @param Translatable|string|null $usageMessage
     * @param array                    $aliases
     */
    public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = []) {
        parent::__construct($name, $description, $usageMessage, $aliases);

        $this->addArgument(
            new FactionCreateArgument('create'),
            new FactionLeaveArgument('leave'),
            new FactionWhoArgument('who')
        );
    }
}