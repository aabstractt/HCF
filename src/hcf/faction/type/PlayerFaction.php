<?php

declare(strict_types=1);

namespace hcf\faction\type;

use hcf\faction\Faction;
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
     * @param float       $dtr
     * @param string|null $startRegen
     * @param string|null $lastRegen
     * @param bool        $regenerating
     * @param array       $allies
     * @param array       $requestedAllies
     * @param array       $invited
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
        float $dtr = 0.0,
        private ?string $startRegen = null,
        private ?string $lastRegen = null,
        private bool $regenerating = false,
        private array $allies = [],
        private array $requestedAllies = [],
        private array $invited = [],
        private bool $open = false,
        private bool $friendlyFire = false,
        private int $lives = 0,
        private ?string $announcement = null
    ) {
        parent::__construct($rowId, $name, $members, $balance, $points, $dtr);
    }

    /**
     * @return string|null
     */
    public function getStartRegen(): ?string {
        return $this->startRegen;
    }

    public function startRegen(): void {
        $this->startRegen = null;
    }

    /**
     * @return string|null
     */
    public function getLastRegen(): ?string {
        return $this->lastRegen;
    }

    public function lastRegen(): void {
        $this->lastRegen = null;
    }

    /**
     * @return bool
     */
    public function isRegenerating(): bool {
        return $this->regenerating;
    }

    /**
     * @param bool $regenerating
     */
    public function setRegenerating(bool $regenerating = false): void {
        $this->regenerating = $regenerating;
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
     * @return array
     */
    public function getInvited(): array {
        return $this->invited;
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
}