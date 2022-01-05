<?php

declare(strict_types=1);

namespace hcf\faction;

use hcf\Placeholders;
use pocketmine\entity\Location;
use hcf\utils\LocUtils;
use pocketmine\world\Position;

class ClaimZone {

    /**
     * @param int      $factionRowId
     * @param Location $firsCorner
     * @param Location $secondCorner
     */
    public function __construct(
        private int $factionRowId,
        private Location $firsCorner,
        private Location $secondCorner
    ) {
    }

    /**
     * @return int
     */
    public function getFactionRowId(): int {
        return $this->factionRowId;
    }

    /**
     * @return Location
     */
    public function getFirsCorner(): Location {
        return $this->firsCorner;
    }

    /**
     * @return Location
     */
    public function getSecondCorner(): Location {
        return $this->secondCorner;
    }

    /**
     * @param Position $pos
     *
     * @return bool
     */
    public function isInside(Position $pos): bool {
        $firstCorner = $this->firsCorner;
        $secondCorner = $this->secondCorner;

        $minX = min($firstCorner->getFloorX(), $secondCorner->getFloorX());
        $maxX = max($firstCorner->getFloorX(), $secondCorner->getFloorX());

        $minZ = min($firstCorner->getFloorZ(), $secondCorner->getFloorZ());
        $maxZ = max($firstCorner->getFloorZ(), $secondCorner->getFloorZ());

        return $minX <= $pos->getFloorX() && $maxX >= $pos->getFloorX() && $minZ <= $pos->getFloorZ() && $maxZ >= $pos->getFloorZ() && $pos->getWorld()->getFolderName() === $firstCorner->getWorld()->getFolderName();
    }

    /**
     * @param array $serialized
     *
     * @return ClaimZone
     */
    public static function deserialize(array $serialized): ClaimZone {
        return new ClaimZone($serialized[2], Placeholders::stringToLocation($serialized[0]), Placeholders::stringToLocation($serialized[1]));
    }
}