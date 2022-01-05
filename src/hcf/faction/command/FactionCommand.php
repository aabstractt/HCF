<?php

declare(strict_types=1);

namespace hcf\faction\command;

use hcf\api\Command;
use hcf\faction\command\argument\captain\FactionInviteArgument;
use hcf\faction\command\argument\coleader\FactionKickArgument;
use hcf\faction\command\argument\coleader\FactionSetHomeArgument;
use hcf\faction\command\argument\FactionCreateArgument;
use hcf\faction\command\argument\FactionJoinArgument;
use hcf\faction\command\argument\FactionWhoArgument;
use hcf\faction\command\argument\leader\FactionDisbandArgument;
use hcf\faction\command\argument\member\FactionDepositArgument;
use hcf\faction\command\argument\member\FactionHomeArgument;
use hcf\faction\command\argument\member\FactionLeaveArgument;
use pocketmine\lang\Translatable;

class FactionCommand extends Command {

    /**
     * @param string                   $name
     * @param Translatable|string      $description
     * @param Translatable|string|null $usageMessage
     * @param array                    $aliases
     */
    public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = []) {
        parent::__construct($name, $description, $usageMessage, $aliases);

        $this->addArgument(
            new FactionCreateArgument('create'),
            new FactionInviteArgument('invite'),
            new FactionKickArgument('kick'),
            new FactionSetHomeArgument('sethome'),
            new FactionDisbandArgument('disband'),
            new FactionHomeArgument('home'),
            new FactionDepositArgument('deposit', ['d']),
            new FactionLeaveArgument('leave'),
            new FactionJoinArgument('join', ['accept']),
            new FactionWhoArgument('who', ['info', 'show'])
        );
    }
}