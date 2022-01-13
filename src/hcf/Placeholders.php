<?php

declare(strict_types=1);

namespace hcf;

use pocketmine\entity\Location;
use pocketmine\plugin\PluginException;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;

class Placeholders {

    /** @var array */
    private static array $placeHolders = [];

    /**
     * @param string $message
     * @param string ...$args
     *
     * @return string
     */
    public static function replacePlaceholders(string $message, string... $args): string {
        if (count(self::$placeHolders) === 0) {
            self::$placeHolders = (new Config(HCF::getInstance()->getDataFolder() . 'messages.yml'))->getAll();
        }

        $message = self::$placeHolders[$message] ?? $message;

        if (is_array($message)) {
            return self::replacePlaceholders(implode("\n", $message), ...$args);
        }

        foreach ($args as $i => $arg) {
            if ($arg === '') $arg = 'None';

            $message = str_replace('{%' . $i . '}' . ($arg === 'Empty' ? "\n" : ''), $arg === 'Empty' ? '' : $arg, $message);
        }

        return TextFormat::colorize($message);
    }

    /**
     * @param int $time
     *
     * @return string
     */
    public static function timeString(int $time): string {
        return $time > 60 ? ($time <= 60 * 60 ? gmdate('i:s', $time) : gmdate("H:i:s", $time)) : $time . 's';
    }

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

        if (($world = Server::getInstance()->getWorldManager()->getWorldByName($split[5])) === null) {
            throw new PluginException('Invalid world');
        }

        return new Location((int) $split[0], (int) $split[1], (int) $split[2], $world, (float) $split[3], (float) $split[4]);
    }

    /**
     * @param World|null $world
     *
     * @return Location
     */
    public static function location(?World $world): Location {
        if ($world === null) {
            throw new PluginException('Invalid world');
        }

        return new Location(0, 0, 0, $world, 0, 0);
    }
}