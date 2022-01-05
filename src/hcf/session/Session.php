<?php

declare(strict_types=1);

namespace hcf\session;

use hcf\faction\Faction;
use hcf\faction\type\FactionRank;
use hcf\session\async\SaveSessionAsync;
use hcf\TaskUtils;
use pocketmine\player\Player;
use pocketmine\plugin\PluginException;
use pocketmine\Server;

class Session {

    /** @var int */
    private int $homeTeleport = -1;

    /**
     * @param string       $xuid
     * @param string       $name
     * @param FactionRank  $factionRank
     * @param Faction|null $faction
     * @param string|null  $lastFactionEdit
     */
    public function __construct(
        private string $xuid,
        private string $name,
        private FactionRank $factionRank,
        private ?Faction $faction = null,
        private ?string $lastFactionEdit = null
    ) {}

    /**
     * @return string
     */
    public function getXuid(): string {
        return $this->xuid;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return FactionRank
     */
    public function getFactionRank(): FactionRank {
        return $this->factionRank;
    }

    /**
     * @param FactionRank $factionRank
     *
     * @return void
     */
    public function setFactionRank(FactionRank $factionRank): void {
        $this->factionRank = $factionRank;
    }

    /**
     * @return Faction|null
     */
    public function getFaction(): ?Faction {
        return $this->faction;
    }

    /**
     * @return Faction
     */
    public function getFactionNonNull(): Faction {
        return $this->faction ?? throw new PluginException('Faction is null');
    }

    /**
     * @param Faction|null $faction
     */
    public function setFaction(?Faction $faction): void {
        $this->faction = $faction;
    }

    /**
     * @return string|null
     */
    public function getLastFactionEdit(): ?string {
        return $this->lastFactionEdit;
    }

    /**
     * @param string|null $lastFactionEdit
     */
    public function setLastFactionEdit(?string $lastFactionEdit): void {
        $this->lastFactionEdit = $lastFactionEdit;
    }

    /**
     * @param int $homeTeleport
     *
     * @return void
     */
    public function setHomeTeleport(int $homeTeleport): void {
        $this->homeTeleport = $homeTeleport;
    }

    /**
     * @return int
     */
    public function getHomeTeleport(): int {
        return $this->homeTeleport;
    }

    /**
     * @return Player
     */
    public function getInstanceNonNull(): Player {
        return Server::getInstance()->getPlayerExact($this->name) ?? throw new PluginException('Player is offline');
    }

    public function save(): void {
        $rowId = -1;

        if ($this->faction !== null) {
            $rowId = $this->faction->getRowId();
        }

        TaskUtils::runAsync(new SaveSessionAsync($this->xuid, $this->name, $rowId, $this->factionRank->ordinal(), 1, 1));
    }
}