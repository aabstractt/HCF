<?php

declare(strict_types=1);

namespace hcf\faction\command\argument;

use hcf\api\Argument;
use hcf\faction\async\SaveFactionAsync;
use hcf\faction\type\FactionMember;
use hcf\faction\type\PlayerFaction;
use hcf\factory\FactionFactory;
use hcf\HCF;
use hcf\Placeholders;
use hcf\session\SessionFactory;
use hcf\TaskUtils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class CreateArgument extends Argument {

    /**
     * @param CommandSender $sender
     * @param string        $commandLabel
     * @param array         $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . 'Run this command in-game');

            return;
        }

        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::RED . 'Usage: /' . $commandLabel . ' create <factionName>');

            return;
        }

        $session = SessionFactory::getInstance()->getPlayerSession($sender);

        if ($session->getFaction() !== null) {
            $sender->sendMessage(Placeholders::replacePlaceholders('COMMAND_FACTION_ATTEMPT_CREATE'));

            return;
        }

        if ($session->getLastFactionEdit() !== null && (time() - 60) < strtotime($session->getLastFactionEdit())) {
            $sender->sendMessage(Placeholders::replacePlaceholders('COMMAND_FACTION_ACTION_COOLDOWN'));
        }

        if (strlen($args[0]) < FactionFactory::getFactionNameMin()) {
            $sender->sendMessage(Placeholders::replacePlaceholders('FACTION_NAME_TOO_SHORT', (string) FactionFactory::getFactionNameMin()));

            return;
        }

        if (strlen($args[0]) > FactionFactory::getFactionNameMax()) {
            $sender->sendMessage(Placeholders::replacePlaceholders('FACTION_NAME_TOO_LONG', (string) FactionFactory::getFactionNameMax()));

            return;
        }

        if (FactionFactory::getInstance()->getPlayerFaction($args[0]) !== null) {
            $sender->sendMessage(Placeholders::replacePlaceholders('FACTION_ALREADY_EXISTS', $args[0]));

            return;
        }

        TaskUtils::runAsync(new SaveFactionAsync(serialize([$args[0], 1.0])), function (SaveFactionAsync $query) use ($args, $session, $sender): void {
            if (!is_int($rowId = $query->getResult())) {
                $sender->sendMessage(TextFormat::RED . 'An error was occurred...');

                return;
            }

            $session->setLastFactionEdit(HCF::dateNow());

            $session->setFaction($faction = new PlayerFaction($rowId, $args[0], [$session->getXuid() => FactionMember::valueOf($session->getXuid(), $session->getName())]));

            $faction->findLeader();

            $session->save();
        });
    }
}