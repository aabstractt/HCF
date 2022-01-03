<?php

declare(strict_types=1);

namespace hcf\faction\type;

use pocketmine\plugin\PluginException;
use pocketmine\utils\EnumTrait;

/**
 * @method static FactionRank LEADER()
 * @method static FactionRank COLEADER()
 * @method static FactionRank CAPTAIN()
 * @method static FactionRank MEMBER()
 */
class FactionRank {

    use EnumTrait;

    /**
     * @return int
     */
    public function ordinal(): int {
        return is_int($ordinal = array_search(mb_strtoupper($this->name()), array_keys(self::$members), true)) ? $ordinal : throw new PluginException('Found invalid ordinal');
    }

    /**
     * @param int $value
     *
     * @return FactionRank
     */
    public static final function valueOf(int $value): FactionRank {
        return array_values(self::getAll())[$value] ?? throw new PluginException('Invalid ordinal value');
    }

    /**
     * Inserts default entries into the registry.
     *
     * (This ought to be private, but traits suck too much for that.)
     */
    protected static function setup(): void {
        self::registerAll(
            new self('member'),
            new self('captain'),
            new self('coleader'),
            new self('leader')
        );
    }
}