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

        if (is_array($message)) {
            return self::replacePlaceholders(implode("\n", $message), ...$args);
        }

        foreach ($args as $i => $arg) {
            if ($arg === '') $arg = 'None';

            $message = str_replace('{%' . $i . '}', $arg === 'Empty' ? '' : $arg, $message);
        }

        return TextFormat::colorize($message);
    }

    /**
     * @param int $time
     *
     * @return string
     */
    public static function timeString(int $time): string {
        return $time <= 60 ? $time . 's' : gmdate("H:i:s", $time);
    }
}