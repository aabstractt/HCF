<?php

declare(strict_types=1);

namespace hcf\listener;

use hcf\faction\type\koth\KothFactory;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;

class BlockPlaceListener implements Listener {

    /**
     * @param BlockPlaceEvent $ev
     *
     * @priority NORMAL
     */
    public function onBlockPlaceEvent(BlockPlaceEvent $ev): void {
        $block = $ev->getBlock();

        foreach (KothFactory::getInstance()->getKoths() as $claimZone) {
            if (!$claimZone->isInside($block->getPosition())) {
                continue;
            }

            $ev->cancel();

            break;
        }
    }
}