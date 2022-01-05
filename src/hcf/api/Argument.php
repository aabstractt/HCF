<?php

declare(strict_types=1);

namespace hcf\api;

use pocketmine\command\CommandSender;

abstract class Argument {

    /**
     * Argument constructor.
     *
     * @param string      $name
     * @param array       $aliases
     * @param string|null $permission
     */
    public function __construct(
        private string $name,
        private array $aliases = [],
        private ?string $permission = null
    ) {}

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getAliases(): array {
        return $this->aliases;
    }

    /**
     * @return string|null
     */
    public function getPermission(): ?string {
        return $this->permission;
    }

    /**
     * @param CommandSender $sender
     * @param string        $commandLabel
     * @param array         $args
     */
    abstract public function execute(CommandSender $sender, string $commandLabel, array $args): void;
}