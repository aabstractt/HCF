<?php

declare(strict_types=1);

namespace hcf\faction\command\argument;

use hcf\command\Argument;
use hcf\faction\async\LoadTopFactionsAsync;
use hcf\Placeholders;
use hcf\task\QueryAsyncTask;
use hcf\TaskUtils;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class FactionTopArgument extends Argument {

    /**
     * @param CommandSender $sender
     * @param string        $commandLabel
     * @param string        $argumentLabel
     * @param array         $args
     */
    public function run(CommandSender $sender, string $commandLabel, string $argumentLabel, array $args): void {
        TaskUtils::runAsync(new LoadTopFactionsAsync(), function (QueryAsyncTask $query) use ($sender): void {
            if (!is_array($result = $query->getResult()) || count($result) === 0) {
                $sender->sendMessage(TextFormat::RED . 'Factions not found');

                return;
            }

            $sender->sendMessage(Placeholders::replacePlaceholders('COMMAND_FACTION_TOP', implode("\n", array_map(function (int $index, array $fetch): string {
                return Placeholders::replacePlaceholders(($index === 0 ? 'FIRST' : 'OTHER') . '_FACTION_TOP', strval($index + 1), $fetch['name'], (string) $fetch['points']);
            }, array_keys($result), $result))));
        });
    }
}