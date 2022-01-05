<?php

declare(strict_types=1);

namespace hcf\faction\command\argument\player;

use hcf\api\Argument;
use hcf\faction\FactionFactory;
use hcf\faction\type\FactionRank;
use hcf\Placeholders;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class FactionWhoArgument extends Argument {

    /**
     * @param CommandSender $sender
     * @param string        $commandLabel
     * @param array         $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        $faction = null;

        if (count($args) > 0) {
            $faction = FactionFactory::getInstance()->getFactionName($args[0]);
        } else if ($sender instanceof Player) {
            $faction = FactionFactory::getInstance()->getPlayerFaction($sender);
        }

        if ($faction === null) {
            $sender->sendMessage(count($args) > 0 ? Placeholders::replacePlaceholders('FACTION_NOT_FOUND', $args[0]) : TextFormat::RED . 'You need use /' . $commandLabel . ' who <faction_name>');

            return;
        }

        /** @var array<int, string> $m */
        $m = [];

        foreach ($faction->getMembers() as $member) {
            $m[$member->getFactionRank()->ordinal()][] = ($member->isOnline() ? TextFormat::GREEN : TextFormat::GRAY) . $member->getName() . sprintf('&e[&a%s&e]', '0');
        }

        $sender->sendMessage(Placeholders::replacePlaceholders('FACTION_WHO_PLAYER',
            $faction->getName(),
            (string) count($faction->getMembers()),
            (string) FactionFactory::getMaxMembers(),
            implode(', ', $m[FactionRank::LEADER()->ordinal()]),
            implode(', ', $m[FactionRank::COLEADER()->ordinal()] ?? []),
            implode(', ', $m[FactionRank::CAPTAIN()->ordinal()] ?? []),
            implode(', ', $m[FactionRank::MEMBER()->ordinal()] ?? []),
            (string) $faction->getBalance(),
            (string) $faction->getDeathsUntilRaidable(true),
            ($remain = $faction->getRemainingRegenerationTime()) <= 0 ? 'Empty' : Placeholders::replacePlaceholders('FACTION_WHO_UNTIL_REGEN', Placeholders::timeString($remain)),
            (string) $faction->getPoints(),
            (string) $faction->getLives(),
            $faction->getAnnouncement() ?? 'None'
        ));
    }
}