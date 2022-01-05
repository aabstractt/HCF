<?php

declare(strict_types=1);

namespace hcf\listener;

use hcf\faction\FactionFactory;
use hcf\faction\type\PlayerFaction;
use hcf\session\SessionFactory;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\Server;

class PlayerDeathListener implements Listener {

    /**
     * @param PlayerDeathEvent $ev
     *
     * @priority NORMAL
     */
    public function onPlayerDeathEvent(PlayerDeathEvent $ev): void {
        $player = $ev->getPlayer();

        $session = SessionFactory::getInstance()->getPlayerSession($player);

        /** @var PlayerFaction $faction */
        $faction = $session->getFactionNonNull();

        $newDtr = $faction->setDeathsUntilRaidable($faction->getDeathsUntilRaidable() - 1.0);

        # dtr-freeze = minutes
        $faction->setRemainingRegenerationTime(FactionFactory::getDtrFreeze() * 60);

        $faction->save();

        Server::getInstance()->broadcastMessage('Faction ' . $faction->getName() . ' dtr: ' . $newDtr . ', regen time: ' . $faction->getRemainingRegenerationTime());
    }
}