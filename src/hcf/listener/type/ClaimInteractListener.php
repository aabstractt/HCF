<?php

declare(strict_types=1);

namespace hcf\listener\type;

use hcf\faction\ClaimZone;
use hcf\faction\FactionFactory;
use hcf\Placeholders;
use hcf\session\Session;
use hcf\session\SessionFactory;
use pocketmine\entity\Location;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\utils\TextFormat;

class ClaimInteractListener implements Listener {

    /**
     * @param PlayerInteractEvent $ev
     *
     * @priority NORMAL
     */
    public function onPlayerInteractEvent(PlayerInteractEvent $ev): void {
        $session = SessionFactory::getInstance()->getPlayerSession($ev->getPlayer());

        if (($claimZone = $session->getClaimZone()) === null) {
            return;
        }

        if (($tag = $ev->getItem()->getNamedTag()->getTag('custom_item')) === null || !in_array($tag->getValue(), ['koth_claiming', 'faction_claiming'], true)) {
            return;
        }

        $ev->cancel();

        $this->handleFactionClaimInteract($session, $claimZone, Location::fromObject($ev->getBlock()->getPosition(), null), $ev->getAction() === $ev::RIGHT_CLICK_BLOCK);
    }

    /**
     * @param Session   $session
     * @param ClaimZone $claimZone
     * @param Location  $loc
     * @param bool      $right
     */
    private function handleFactionClaimInteract(Session $session, ClaimZone $claimZone, Location $loc, bool $right): void {
        $instance = $session->getInstanceNonNull();

        if (FactionFactory::getInstance()->getFactionAt($loc) !== null) {
            $instance->sendMessage(Placeholders::replacePlaceholders('CANNOT_CLAIM_HERE'));

            return;
        }

        if ($right) {
            $claimZone->setFirsCorner($loc);
        } else {
            $claimZone->setSecondCorner($loc);
        }

        $instance->sendMessage(Placeholders::replacePlaceholders('CLAIMING_' . ($right ? 'FIRST' : 'SECOND') . '_POSITION', (string) $loc->x, (string) $loc->z));

        if ($claimZone->getFirsCorner()->getFloorY() === 0 || $claimZone->getSecondCorner()->getFloorY() === 0) {
            return;
        }

        if (($distance = $claimZone->getFirsCorner()->distance($claimZone->getSecondCorner())) < 5 && $claimZone->getFactionRowId() !== -1) {
            $instance->sendMessage(Placeholders::replacePlaceholders('CLAIM_INVALID_SIZE'));

            return;
        }

        if ($claimZone->getFactionRowId() !== -1) {
            $instance->sendMessage(Placeholders::replacePlaceholders('CLAIMING_COST', (string) $distance));
        } else {
            $instance->sendMessage(TextFormat::GREEN . "Type 'accept' into chat");
        }
    }
}