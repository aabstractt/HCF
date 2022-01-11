<?php

declare(strict_types=1);

namespace hcf\faction;

use hcf\Placeholders;
use pocketmine\entity\Location;
use pocketmine\math\AxisAlignedBB;
use pocketmine\world\Position;
use pocketmine\world\World;

class ClaimZone {

    /** @var string */
    public const SPAWN = 'Spawn';
    public const NETHER_SPAWN = 'Nether Spawn';
    public const ROAD = 'Road';
    public const KOTH = 'Koth';
    public const WARZONE = 'Warzone';
    public const WILDERNESS = 'Wilderness';

    /** @var bool */
    public bool $created = false;
    /** @var string */
    public string $specify = '';

    /**
     * @param int      $factionRowId
     * @param Location $firsCorner
     * @param Location $secondCorner
     */
    public function __construct(
        private int $factionRowId,
        private Location $firsCorner,
        private Location $secondCorner
    ) {}

    /**
     * @return int
     */
    public function getFactionRowId(): int {
        return $this->factionRowId;
    }

    /**
     * @return World
     */
    public function getWorld(): World {
        return $this->firsCorner->getWorld();
    }

    /**
     * @param Location $firsCorner
     */
    public function setFirsCorner(Location $firsCorner): void {
        $this->firsCorner = $firsCorner;
    }

    /**
     * @return Location
     */
    public function getFirsCorner(): Location {
        return $this->firsCorner;
    }

    /**
     * @param Location $secondCorner
     */
    public function setSecondCorner(Location $secondCorner): void {
        $this->secondCorner = $secondCorner;
    }

    /**
     * @return Location
     */
    public function getSecondCorner(): Location {
        return $this->secondCorner;
    }

    /**
     * @param bool $y
     *
     * @return AxisAlignedBB
     */
    public function asAxisAligned(bool $y = true): AxisAlignedBB {
        $firstCorner = $this->firsCorner;
        $secondCorner = $this->secondCorner;

        return new AxisAlignedBB(
            min($firstCorner->getFloorX(), $secondCorner->getFloorX()),
            $y ? min($firstCorner->getFloorY(), $secondCorner->getFloorY()) : 0,
            min($firstCorner->getFloorZ(), $secondCorner->getFloorZ()),
            max($firstCorner->getFloorX(), $secondCorner->getFloorX()),
            $y ? max($firstCorner->getFloorY(), $secondCorner->getFloorY()) : World::Y_MAX,
            max($firstCorner->getFloorZ(), $secondCorner->getFloorZ())
        );
    }

    /**
     * @param Position $pos
     *
     * @return bool
     */
    public function isInside(Position $pos): bool {
        return $this->asAxisAligned(false)->isVectorInside($pos) && $pos->getWorld()->getFolderName() === $this->getWorld()->getFolderName();
    }

    /**
     * @param array $serialized
     *
     * @return ClaimZone
     */
    public static function deserialize(array $serialized): ClaimZone {
        return new ClaimZone($serialized[2] ?? -1, Placeholders::stringToLocation($serialized[0]), Placeholders::stringToLocation($serialized[1]));
    }
}