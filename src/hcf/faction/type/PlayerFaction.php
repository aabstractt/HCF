<?php

declare(strict_types=1);

namespace hcf\faction\type;

use hcf\faction\Faction;
use hcf\faction\FactionFactory;
use pocketmine\plugin\PluginException;

class PlayerFaction extends Faction {

    /** @var FactionMember|null */
    private ?FactionMember $leader = null;

    /**
     * @param int         $rowId
     * @param string      $name
     * @param array       $members
     * @param int         $balance
     * @param int         $points
     * @param float       $deathsUntilRaidable
     * @param int         $regenCooldown
     * @param float       $lastDtrUpdate
     * @param array       $allies
     * @param array       $requestedAllies
     * @param bool        $open
     * @param bool        $friendlyFire
     * @param int         $lives
     * @param string|null $announcement
     */
    public function __construct(
        int $rowId,
        string $name,
        array $members = [],
        int $balance = 0,
        int $points = 0,
        float $deathsUntilRaidable = 0.0,
        private int $regenCooldown = 0,
        private float $lastDtrUpdate = 0,
        private array $allies = [],
        private array $requestedAllies = [],
        private bool $open = false,
        private bool $friendlyFire = false,
        private int $lives = 0,
        private ?string $announcement = null
    ) {
        parent::__construct($rowId, $name, $members, $balance, $points, $deathsUntilRaidable);
    }

    /**
     * @return FactionMember
     */
    public function getLeader(): FactionMember {
        return $this->leader ?? throw new PluginException('Leader not found');
    }

    public function findLeader(): void {
        $filter = array_filter($this->members, fn(FactionMember $member) => $member->getFactionRank() === FactionRank::LEADER());

        $this->leader = $filter[array_key_first($filter)] ?? null;
    }

    /**
     * @param FactionMember $member
     *
     * @return void
     */
    public function setLeader(FactionMember $member): void {
        $this->leader = $member;
    }

    /**
     * @return array
     */
    public function getAllies(): array {
        return $this->allies;
    }

    /**
     * @return array
     */
    public function getRequestedAllies(): array {
        return $this->requestedAllies;
    }

    /**
     * @return bool
     */
    public function isOpen(): bool {
        return $this->open;
    }

    /**
     * @param bool $open
     */
    public function setOpen(bool $open): void {
        $this->open = $open;
    }

    /**
     * @return bool
     */
    public function isFriendlyFire(): bool {
        return $this->friendlyFire;
    }

    /**
     * @param bool $friendlyFire
     */
    public function setFriendlyFire(bool $friendlyFire): void {
        $this->friendlyFire = $friendlyFire;
    }

    /**
     * @return int
     */
    public function getLives(): int {
        return $this->lives;
    }

    /**
     * @param int $lives
     */
    public function setLives(int $lives): void {
        $this->lives = $lives;
    }

    /**
     * @return string|null
     */
    public function getAnnouncement(): ?string {
        return $this->announcement;
    }

    /**
     * @return float
     */
    public function getMaximumDeathsUntilRaidable(): float {
        return count($this->getMembers()) === 1 ? 1.1 : min(FactionFactory::getMaxDtr(), count($this->getMembers()) * FactionFactory::getDtrPerPlayer());
    }

    /**
     * @param bool $updateLastCheck
     *
     * @return float
     */
    public function getDeathsUntilRaidable(bool $updateLastCheck = false): float {
        if ($updateLastCheck) {
            $this->updateDeathsUntilRaidable();
        }

        return $this->deathsUntilRaidable;
    }

    public function updateDeathsUntilRaidable(): void {
        if ($this->getRegenStatus() !== FactionFactory::STATUS_REGENERATING) {
            return;
        }

        $timePassed = ($now = time()) - $this->getLastDtrUpdate();

        if ($timePassed >= ($dtrUpdate = FactionFactory::getDtrUpdate())) {
            $remainder = $timePassed % $dtrUpdate;

            $multiplier = ($timePassed + $remainder) / $dtrUpdate;

            $increase = $multiplier * FactionFactory::getDtrIncrementBetweenUpdate();

            $this->setDeathsUntilRaidable($this->getDeathsUntilRaidable() + $increase);

            $this->lastDtrUpdate = $now;

            //$this->save();
        }
    }

    /**
     * @param float $deathsUntilRaidable
     * @param bool  $limit
     *
     * @return float
     */
    public function setDeathsUntilRaidable(float $deathsUntilRaidable, bool $limit = true): float {
        $deathsUntilRaidable = round($deathsUntilRaidable * 100.0, 1) / 100.0;

        if ($limit) {
            $deathsUntilRaidable = min($deathsUntilRaidable, $this->getMaximumDeathsUntilRaidable());
        }

        if (abs($deathsUntilRaidable - $this->getDeathsUntilRaidable()) !== 0.0) {
            $deathsUntilRaidable = round($deathsUntilRaidable * 100.0) / 100.0;

            if ($deathsUntilRaidable <= 0) {
                echo 'Raidable' . PHP_EOL;
            }

            $this->lastDtrUpdate = time();

            $this->deathsUntilRaidable = $deathsUntilRaidable;

            $this->save();
        }

        return $this->deathsUntilRaidable;
    }

    /**
     * @param int $time
     */
    public function setRemainingRegenerationTime(int $time): void {
        $this->regenCooldown = ($now = time()) + $time;

        $this->lastDtrUpdate = $now + (FactionFactory::getDtrUpdate() * 2);
    }

    /**
     * @return int
     */
    public function getRemainingRegenerationTime(): int {
        return $this->regenCooldown === 0 ? 0 : $this->regenCooldown - time();
    }

    /**
     * @return int
     */
    public function getRegenStatus(): int {
        if ($this->getRemainingRegenerationTime() > 0) {
            return FactionFactory::STATUS_PAUSED;
        }

        if ($this->getMaximumDeathsUntilRaidable() > $this->getDeathsUntilRaidable()) {
            return FactionFactory::STATUS_REGENERATING;
        }

        return FactionFactory::STATUS_FULL;
    }

    /**
     * @return float
     */
    private function getLastDtrUpdate(): float {
        return $this->lastDtrUpdate;
    }
}