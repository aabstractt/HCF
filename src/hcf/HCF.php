<?php

declare(strict_types=1);

namespace hcf;

use hcf\event\EventHeartbeat;
use hcf\event\sotw\command\SotwCommand;
use hcf\faction\command\FactionCommand;
use hcf\faction\FactionFactory;
use hcf\koth\command\KothCommand;
use hcf\koth\KothFactory;
use hcf\listener\BlockBreakListener;
use hcf\listener\BlockPlaceListener;
use hcf\listener\EntityDamageListener;
use hcf\listener\PlayerDeathListener;
use hcf\listener\PlayerJoinListener;
use hcf\listener\PlayerMoveListener;
use hcf\listener\PlayerQuitListener;
use hcf\listener\type\ClaimChatListener;
use hcf\listener\type\ClaimInteractListener;
use hcf\task\ScoreboardUpdateTask;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\World;
use pocketmine\world\WorldException;

class HCF extends PluginBase {

    use SingletonTrait;

    protected function onLoad(): void {
        self::setInstance($this);

        $this->saveDefaultConfig();
        $this->saveResource("messages.yml", self::isUnderDevelopment());
    }

    public function onEnable(): void {
        FactionFactory::getInstance()->init();
        KothFactory::getInstance()->init();

        $this->registerCommand(
            new FactionCommand("faction", "Faction commands", null, ["f"]),
            new KothCommand('koth', 'Koth Management'),
            new SotwCommand()
        );

        $this->registerListener(
            new PlayerJoinListener(),
            new PlayerQuitListener(),
            new EntityDamageListener(),
            new PlayerDeathListener(),
            new PlayerMoveListener(),
            new PlayerDeathListener(),
            new BlockPlaceListener(),
            new BlockBreakListener(),
            new ClaimInteractListener(),
            new ClaimChatListener()
        );

        $this->getScheduler()->scheduleRepeatingTask(new ScoreboardUpdateTask(), 20); // 1 tick
    }

    /**
     * @param Command ...$commands
     */
    private function registerCommand(Command ...$commands): void {
        foreach($commands as $command) {
            $this->getServer()->getCommandMap()->register("hcf", $command);
        }
    }

    /**
     * @param Listener ...$listeners
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
     * @return World
     */
    public static function getDefaultWorld(): World {
        return Server::getInstance()->getWorldManager()->getDefaultWorld() ?? throw new WorldException('Default world was received null');
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
}