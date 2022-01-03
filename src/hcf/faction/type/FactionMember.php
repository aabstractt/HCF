<?php

declare(strict_types=1);

namespace hcf\faction\type;

class FactionMember {

    /**
     * @param string      $xuid
     * @param string      $name
     * @param FactionRank $factionRank
     */
    public function __construct(
        private string $xuid,
        private string $name,
        private FactionRank $factionRank
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
     * @return bool
     */
    public function isAtLeast(FactionRank $factionRank): bool {
        return $this->factionRank->ordinal() >= $factionRank->ordinal();
    }

    /**
     * @return string
     */
    public function getStars(): string {
        if ($this->factionRank->ordinal() === FactionRank::LEADER()->ordinal() || $this->factionRank->ordinal() === FactionRank::COLEADER()->ordinal()) {
            return '**';
        }

        if ($this->factionRank->ordinal() === FactionRank::CAPTAIN()->ordinal()) {
            return '*';
        }

        return '';
    }

    /**
     * @param mixed... $data
     *
     * @return FactionMember
     */
    public static function valueOf(mixed... $data): FactionMember {
        return new self(strval($data[0]), strval($data[1]), count($data) === 3 ? FactionRank::valueOf(intval($data[2])) : FactionRank::LEADER());
    }
}