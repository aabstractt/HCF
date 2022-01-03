<?php

declare(strict_types=1);

namespace hcf\utils;

use pocketmine\entity\Location;
use pocketmine\plugin\PluginException;
use pocketmine\Server;

class LocUtils {

    /**
     * @param Location $l
     *
     * @return string
     */
    public static function locationToString(Location $l): string {
        return $l->getFloorX() . ';' . $l->getFloorY() . ';' . $l->getFloorZ() . ';' . $l->yaw . ';' . $l->pitch . ';' . $l->getWorld()->getFolderName();
    }

    /**
     * @param string $input
     *
     * @return Location
     */
    public static function stringToLocation(string $input): Location {
        $split = explode(';', $input);

        if (count($split) < 6) {
            throw new PluginException('Invalid string');
        }

        $world = Server::getInstance()->getWorldManager()->getWorldByName($split[5]);

        if ($world === null) {
            throw new PluginException('Invalid world');
        }

        return new Location((int) $split[0], (int) $split[1], (int) $split[2], $world, (float) $split[3], (float) $split[4]);
    }
}