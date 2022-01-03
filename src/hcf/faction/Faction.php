<?php

declare(strict_types=1);

namespace hcf\faction;

use hcf\faction\type\FactionMember;

class Faction {

    /**
     * @param string $name
     * @param int    $rowId
     * @param array  $members
     * @param int    $balance
     * @param int    $points
     * @param float  $dtr
     */
    public function __construct(
        protected int $rowId,
        protected string $name,
        protected array $members = [],
        protected int $balance = 0,
        protected int $points = 0,
        protected float $dtr = 0.0
    ) {}

    /**
     * @return int
     */
    public function getRowId(): int {
        return $this->rowId;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getMembers(): array {
        return $this->members;
    }

    /**
     * @param FactionMember $factionMember
     *
     * @return void
     */
    public function addMember(FactionMember $factionMember): void {
        if (!$this->isMember($factionMember->getXuid())) {
            return;
        }

        $this->members[$factionMember->getXuid()] = $factionMember;
    }

    /**
     * @param FactionMember $factionMember
     *
     * @return void
     */
    public function removeMember(FactionMember $factionMember): void {
        if (!$this->isMember($factionMember->getXuid())) {
            return;
        }

        unset($this->members[$factionMember->getXuid()]);
    }

    /**
     * @param string $xuid
     *
     * @return bool
     */
    public function isMember(string $xuid): bool {
        return isset($this->members[$xuid]);
    }

    /**
     * @param string $xuid
     *
     * @return FactionMember|null
     */
    public function getMember(string $xuid): ?FactionMember {
        return $this->members[$xuid] ?? null;
    }

    /**
     * @return int
     */
    public function getBalance(): int {
        return $this->balance;
    }

    /**
     * @param int $increase
     *
     * @return void
     */
    public function increaseBalance(int $increase = 1): void {
        $this->balance += $increase;
    }

    /**
     * @param int $decrease
     *
     * @return void
     */
    public function decreaseBalance(int $decrease = 1): void {
        $this->balance += $decrease;
    }

    /**
     * @return float
     */
    public function getDtr(): float {
        return $this->dtr;
    }

    /**
     * @return int
     */
    public function getPoints(): int {
        return $this->points;
    }

    /**
     * @param int $increase
     *
     * @return void
     */
    public function increasePoints(int $increase = 1): void {
        $this->points += $increase;
    }

    /**
     * @param int $decrease
     *
     * @return void
     */
    public function decreasePoints(int $decrease = 1): void {
        $this->points -= $decrease;
    }
}