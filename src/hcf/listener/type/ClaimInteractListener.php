<?php

declare(strict_types=1);

namespace hcf\listener\type;

use hcf\faction\FactionFactory;
use hcf\Placeholders;
use hcf\session\SessionFactory;
use pocketmine\entity\Location;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;

class ClaimInteractListener implements Listener {

    /**
     * @param PlayerInteractEvent $ev
     *
     * @priority NORMAL
     */
    public function onPlayerInteractEvent(PlayerInteractEvent $ev): void {
        $session = SessionFactory::getInstance()->getPlayerSession($player = $ev->getPlayer());

        if (($claimZone = $session->getClaimZone()) === null) {
            return;
        }

        $item = $ev->getItem();

        if (($tag = $item->getNamedTag()->getTag('custom_item')) === null || $tag->getValue() !== 'claiming') {
            return;
        }

        $block = $ev->getBlock();

        $ev->cancel();

        if (FactionFactory::getInstance()->getFactionAt($block->getPosition()) !== null) {
            $player->sendMessage(Placeholders::replacePlaceholders('CANNOT_CLAIM_HERE'));

            return;
        }

        $loc = Location::fromObject($block->getPosition(), null);

        if ($first = $ev->getAction() === $ev::RIGHT_CLICK_BLOCK) {
            $claimZone->setFirsCorner($loc);
        } else {
            $claimZone->setSecondCorner($loc);
        }

        $player->sendMessage(Placeholders::replacePlaceholders('CLAIMING_' . ($first ? 'FIRST' : 'SECOND') . '_POSITION', (string) $loc->x, (string) $loc->z));

        if ($claimZone->getFirsCorner()->getFloorY() === 0 || $claimZone->getSecondCorner()->getFloorY() === 0) {
            return;
        }

        if (($distance = $claimZone->getFirsCorner()->distance($claimZone->getSecondCorner())) < 5) {
            $player->sendMessage(Placeholders::replacePlaceholders('CLAIM_INVALID_SIZE'));

            return;
        }

        $player->sendMessage(Placeholders::replacePlaceholders('CLAIMING_COST', (string) $distance));
    }
}