<?php

declare(strict_types=1);

namespace hcf\faction;

use hcf\faction\async\SaveFactionAsync;
use hcf\faction\type\FactionMember;
use hcf\TaskUtils;
use hcf\utils\Serializable;
use pocketmine\entity\Location;
use pocketmine\Server;

class Faction extends Serializable {

    /** @var Location|null */
    private ?Location $homePosition;
    /** @var array */
    private array $invited = [];

    /**
     * @param int             $rowId
     * @param string          $name
     * @param FactionMember[] $members
     * @param int             $balance
     * @param int             $points
     * @param float           $deathsUntilRaidable
     */
    public function __construct(
        protected int $rowId,
        protected string $name,
        protected array $members = [],
        protected int $balance = 0,
        protected int $points = 0,
        protected float $deathsUntilRaidable = 0.0
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
     * @return bool
     */
    public function isRaidable(): bool {
        return $this->deathsUntilRaidable <= 0.0;
    }

    /**
     * @return FactionMember[]
     */
    public function getMembers(): array {
        return $this->members;
    }

    /**
     * @param FactionMember $factionMember
     */
    public function addMember(FactionMember $factionMember): void {
        if ($this->isMember($factionMember->getXuid())) {
            return;
        }

        $this->members[$factionMember->getXuid()] = $factionMember;
    }

    /**
     * @param string $xuid
     */
    public function removeMember(string $xuid): void {
        if (!$this->isMember($xuid)) {
            return;
        }

        unset($this->members[$xuid]);
    }

    /**
     * @param string $xuid
     *
     * @return bool
     */
    public function isMember(string $xuid): bool {
        return $this->getMember($xuid) !== null;
    }

    /**
     * @param string $xuid
     *
     * @return FactionMember|null
     */
    public function getMember(string $xuid): ?FactionMember {
        return $this->members[$xuid] ?? array_values(array_filter($this->members, fn(FactionMember $member) => strtolower($member->getName()) === strtolower($xuid)))[0] ?? null;
    }

    /**
     * @param string $xuid
     *
     * @return bool
     */
    public function isAlreadyInvited(string $xuid): bool {
        return in_array($xuid, $this->invited, true);
    }

    /**
     * @param string $xuid
     *
     * @return void
     */
    public function addInvite(string $xuid): void {
        $this->invited[] = $xuid;
    }

    /**
     * @param string $xuid
     *
     * @return void
     */
    public function removeInvite(string $xuid): void {
        $this->invited = array_diff($this->invited, [$xuid]);
    }

    /**
     * @return int
     */
    public function getBalance(): int {
        return $this->balance;
    }

    /**
     * @param int $increase
     */
    public function increaseBalance(int $increase = 1): void {
        $this->balance += $increase;
    }

    /**
     * @param int $decrease
     */
    public function decreaseBalance(int $decrease = 1): void {
        $this->balance += $decrease;
    }

    /**
     * @return int
     */
    public function getPoints(): int {
        return $this->points;
    }

    /**
     * @param int $increase
     */
    public function increasePoints(int $increase = 1): void {
        $this->points += $increase;
    }

    /**
     * @param int $decrease
     */
    public function decreasePoints(int $decrease = 1): void {
        $this->points -= $decrease;
    }

    /**
     * @param string $message
     */
    public function broadcastMessage(string $message): void {
        foreach ($this->members as $factionMember) {
            if (($player = Server::getInstance()->getPlayerExact($factionMember->getName())) === null) {
                continue;
            }

            $player->sendMessage($message);
        }
    }

    /**
     * @param Location $pos
     */
    public function setHomePosition(Location $pos): void {
        $this->homePosition = $pos;
    }

    /**
     * @return Location|null
     */
    public function getHomePosition(): ?Location {
        return $this->homePosition;
    }

    public function save(): void {
        TaskUtils::runAsync(new SaveFactionAsync($this->serializeString()));
    }

    /**
     * @param array $merge
     * @param bool  $static
     *
     * @return string
     */
    public function serializeString(array $merge = [], bool $static = false): string {
        $serialized = $this->serialize($merge, $static);

        unset($serialized['members'], $serialized['leader']);

        return serialize($serialized);
    }
}