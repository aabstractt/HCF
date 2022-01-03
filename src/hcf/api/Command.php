<?php

declare(strict_types=1);

namespace hcf\api;

use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\utils\TextFormat;

abstract class Command extends \pocketmine\command\Command {

    /** @var Argument[] */
    private array $arguments = [];

    protected function addArgument(Argument ...$arguments): void {
        foreach ($arguments as $argument) {
            $this->arguments[$argument->getName()] = $argument;
        }
    }

    /**
     * @param string $label
     *
     * @return Argument|null
     */
    protected function getArgument(string $label): ?Argument {
        return $this->arguments[strtolower($label)] ?? null;
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param string[] $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (count($this->arguments) !== 0 && count($args) === 0) {
            throw new InvalidCommandSyntaxException();
        }

        $name = array_shift($args);

        if ($name === null) {
            throw new InvalidCommandSyntaxException();
        }

        $command = $this->getArgument($name);

        if ($command === null) {
            throw new InvalidCommandSyntaxException();
        }

        if (($permission = $command->getPermission()) !== null && !$sender->hasPermission($permission)) {
            $sender->sendMessage(TextFormat::RED . 'You don\'t have permissions to use this command');

            return;
        }

        $command->execute($sender, $commandLabel, $args);
    }
}