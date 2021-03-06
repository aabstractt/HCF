<?php

declare(strict_types=1);

namespace hcf\faction\type\koth\command;

use hcf\command\Command;
use hcf\faction\type\koth\command\argument\KothCreateArgument;
use hcf\faction\type\koth\command\argument\KothListArgument;
use hcf\faction\type\koth\command\argument\KothStartArgument;
use hcf\faction\type\koth\command\argument\KothTimeArgument;
use pocketmine\lang\Translatable;

class KothCommand extends Command {

    /**
     * @param string                   $name
     * @param Translatable|string      $description
     * @param Translatable|string|null $usageMessage
     * @param array                    $aliases
     */
    public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = []) {
        parent::__construct($name, $description, $usageMessage, $aliases);

        $this->addArgument(
            new KothCreateArgument('create', [], 'koth.command.create'),
            new KothTimeArgument('time', [], 'koth.command.time'),
            new KothStartArgument('start', [], 'koth.command.start'),
            new KothListArgument('list')
        );
    }
}