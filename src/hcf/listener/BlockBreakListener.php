<?php

declare(strict_types=1);

namespace hcf\listener;

use hcf\faction\type\koth\KothFactory;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;

class BlockBreakListener implements Listener {

    /**
     * @param BlockBreakEvent $ev
     *
     * @priority NORMAL
     */
    public function onBlockBreakEvent(BlockBreakEvent $ev): void {
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