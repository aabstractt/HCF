<?php

declare(strict_types=1);

namespace hcf\faction\command;

use hcf\command\Command;
use hcf\faction\command\argument\admin\FactionAddBalanceArgument;
use hcf\faction\command\argument\admin\FactionDecreaseBalanceArgument;
use hcf\faction\command\argument\admin\FactionDecreasePointsArgument;
use hcf\faction\command\argument\admin\FactionForceCreateArgument;
use hcf\faction\command\argument\admin\FactionForceDisbandArgument;
use hcf\faction\command\argument\admin\FactionForceJoinArgument;
use hcf\faction\command\argument\admin\FactionForceUnclaimArgument;
use hcf\faction\command\argument\admin\FactionSetDtrArgument;
use hcf\faction\command\argument\admin\FactionAddPointsArgument;
use hcf\faction\command\argument\captain\FactionInviteArgument;
use hcf\faction\command\argument\coleader\FactionClaimArgument;
use hcf\faction\command\argument\coleader\FactionDemoteArgument;
use hcf\faction\command\argument\coleader\FactionKickArgument;
use hcf\faction\command\argument\coleader\FactionPromoteArgument;
use hcf\faction\command\argument\coleader\FactionSetHomeArgument;
use hcf\faction\command\argument\coleader\FactionWithdrawArgument;
use hcf\faction\command\argument\FactionCreateArgument;
use hcf\faction\command\argument\FactionJoinArgument;
use hcf\faction\command\argument\FactionTopArgument;
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
            new FactionForceCreateArgument('forcecreate', [], 'faction.admin.forcecreate'),
            new FactionForceDisbandArgument('forcedisband', [], 'faction.admin.forcedisband'),
            new FactionSetDtrArgument('setdtr', [], 'faction.admin.setdtr'),
            new FactionForceJoinArgument('forcejoin', [], 'faction.admin.forcejoin'),
            new FactionForceUnclaimArgument('forceunclaim', [], 'faction.admin.forceunclaim'),
            new FactionAddPointsArgument('addpoints', [], 'faction.admin.addpoints'),
            new FactionDecreasePointsArgument('decreasepoints', ['rempoints'], 'faction.admin.decreasepoints'),
            new FactionAddBalanceArgument('addbalance', ['addbal'], 'faction.admin.addbalance'),
            new FactionDecreaseBalanceArgument('decreasebalance', ['rembal'], 'faction.admin.decreasebalance'),
            new FactionCreateArgument('create'),
            new FactionInviteArgument('invite'),
            new FactionKickArgument('kick'),
            new FactionClaimArgument('claim'),
            new FactionDemoteArgument('demote'),
            new FactionPromoteArgument('promote'),
            new FactionSetHomeArgument('sethome'),
            new FactionWithdrawArgument('withdraw'),
            new FactionDisbandArgument('disband'),
            new FactionHomeArgument('home'),
            new FactionDepositArgument('deposit', ['d']),
            new FactionLeaveArgument('leave'),
            new FactionJoinArgument('join', ['accept']),
            new FactionWhoArgument('who', ['info', 'show']),
            new FactionTopArgument('top')
        );
    }
}