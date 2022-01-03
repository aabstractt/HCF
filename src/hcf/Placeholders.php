<?php

declare(strict_types=1);

namespace hcf;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

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

        foreach ($args as $i => $arg) {
            $message = str_replace('{%' . $i . '}', $arg, $message);
        }

        return TextFormat::colorize($message);
    }
}