<?php

declare(strict_types=1);

namespace hcf\listener\type;

use hcf\faction\async\AddClaimAsync;
use hcf\faction\FactionFactory;
use hcf\Placeholders;
use hcf\session\SessionFactory;
use hcf\TaskUtils;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\item\VanillaItems;

class ClaimChatListener implements Listener {

    /**
     * @param PlayerChatEvent $ev
     *
     * @priority NORMAL
     */
    public function onPlayerChatEvent(PlayerChatEvent $ev): void {
        $session = SessionFactory::getInstance()->getPlayerSession($player = $ev->getPlayer());

        if (($claimZone = $session->getClaimZone()) === null) {
            return;
        }

        if (strtolower($ev->getMessage()) === 'accept') {
            $ev->cancel();

            if ($claimZone->getFirsCorner()->y <= 0 || $claimZone->getSecondCorner()->y <= 0) {
                $player->sendMessage(Placeholders::replacePlaceholders('CLAIMING_NOT_SELECTED'));

                return;
            }

            if (($faction = $session->getFaction()) === null) {
                $player->sendMessage(Placeholders::replacePlaceholders('COMMAND_FACTION_NOT_IN'));

                $session->setClaimZone(null);

                return;
            }

            if (($distance = $claimZone->getFirsCorner()->distance($claimZone->getSecondCorner())) < 5) {
                $player->sendMessage(Placeholders::replacePlaceholders('CLAIM_INVALID_SIZE'));

                return;
            }

            if ($distance > $faction->getBalance()) {
                $player->sendMessage(Placeholders::replacePlaceholders('FACTION_NOT_ENOUGH_BALANCE'));

                return;
            }

            $session->setClaimZone(null);

            $player->getInventory()->remove(VanillaItems::GOLDEN_HOE());

            TaskUtils::runAsync(new AddClaimAsync(
                $faction->getRowId(),
                Placeholders::locationToString($claimZone->getFirsCorner()),
                Placeholders::locationToString($claimZone->getSecondCorner())
            ), function (AddClaimAsync $query) use ($player, $claimZone, $faction, $distance): void {
                $faction->decreaseBalance((int) $distance);
                $faction->save();

                $faction->setClaimZone($claimZone);

                FactionFactory::getInstance()->addClaim($claimZone);

                $player->sendMessage(Placeholders::replacePlaceholders('FACTION_CLAIM_COMPLETED'));
            });
        }
    }
}