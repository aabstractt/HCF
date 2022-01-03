<?php

declare(strict_types=1);

namespace hcf;

use hcf\faction\command\FactionCommand;
use hcf\faction\type\FactionRank;
use hcf\factory\FactionFactory;
use hcf\listener\PlayerJoinListener;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class HCF extends PluginBase {

    use SingletonTrait;

    public function onEnable(): void {
        self::setInstance($this);

        $this->saveDefaultConfig();
        $this->saveResource('messages.yml');

        FactionFactory::getInstance()->init();

        $this->getServer()->getCommandMap()->register('faction', new FactionCommand('faction', 'Faction commands', '', ['f']));

        $this->registerListener(
            new PlayerJoinListener()
        );
    }

    /**
     * @param Listener ...$listeners
     *
     * @return void
     */
    protected function registerListener(Listener ...$listeners): void {
        foreach ($listeners as $listener) {
            $this->getServer()->getPluginManager()->registerEvents($listener, $this);
        }
    }

    /**
     * @return string
     */
    public static function dateNow(): string {
        return '';
    }
}