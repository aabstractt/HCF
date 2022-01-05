<?php

declare(strict_types=1);

namespace hcf;

use hcf\event\EventHeartbeat;
use hcf\event\EventManager;
use hcf\event\sotw\command\SotwCommand;
use hcf\faction\command\FactionCommand;
use hcf\faction\FactionFactory;
use hcf\listener\PlayerDeathListener;
use hcf\listener\PlayerJoinListener;
use hcf\listener\PlayerQuitListener;
use hcf\listener\SotwListener;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class HCF extends PluginBase {

    use SingletonTrait;

    private EventManager $event_manager;

    protected function onLoad(): void {
        self::setInstance($this);

        $this->saveDefaultConfig();
        $this->saveResource("messages.yml", self::isUnderDevelopment());
    }

    public function onEnable(): void {
        FactionFactory::getInstance()->init();
        $this->event_manager = new EventManager();

        $this->registerCommand(
            new FactionCommand("faction", "Faction commands", null, ["f"]),
            new SotwCommand()
        );
        $this->registerListener(
            new PlayerJoinListener(),
            new PlayerQuitListener(),
            new PlayerDeathListener(),
            new SotwListener()
        );

        $this->getScheduler()->scheduleRepeatingTask(new EventHeartbeat(), 20); // 1 tick
    }

    private function registerCommand(Command ...$commands) {
        foreach($commands as $command) {
            $this->getServer()->getCommandMap()->register("hcf", $command);
        }
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

    /**
     * @param string $key
     * @param int    $defaultValue
     *
     * @return int
     */
    public function getInt(string $key, int $defaultValue = 0): int {
        return is_int(($value = $this->getConfig()->getNested($key, $defaultValue))) ? $value : $defaultValue;
    }

    /**
     * @param string $key
     * @param float  $defaultValue
     *
     * @return float
     */
    public function getFloat(string $key, float $defaultValue = 0.0): float {
        return is_float(($value = $this->getConfig()->getNested($key, $defaultValue))) ? $value : $defaultValue;
    }

    /**
     * @param string      $key
     * @param string|null $defaultValue
     *
     * @return string|null
     */
    public function getString(string $key, string $defaultValue = null): ?string {
        return is_string(($value = $this->getConfig()->getNested($key, $defaultValue))) ? $value : $defaultValue;
    }

    /**
     * @param string $key
     * @param array  $defaultValue
     *
     * @return array
     */
    public function getArray(string $key, array $defaultValue = []): array {
        return is_array(($value = $this->getConfig()->getNested($key, $defaultValue))) ? $value : $defaultValue;
    }

    /**
     * @return bool
     */
    public static function isUnderDevelopment(): bool {
        return true;
    }

    public function getEventManager(): EventManager {
        return $this->event_manager;
    }

}