<?php

declare(strict_types=1);

namespace hcf\session;

use hcf\faction\ClaimZone;
use hcf\faction\Faction;
use hcf\faction\FactionFactory;
use hcf\faction\type\FactionRank;
use hcf\session\async\SaveSessionAsync;
use hcf\session\scoreboard\ScoreboardBuilder;
use hcf\TaskUtils;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\plugin\PluginException;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class Session {

    /** @var int */
    private int $homeTeleport = -1;
    /** @var ClaimZone|null */
    private ?ClaimZone $claimZone = null;
    /** @var ScoreboardBuilder */
    private ScoreboardBuilder $scoreboardBuilder;

    /**
     * @param string      $xuid
     * @param string      $name
     * @param FactionRank $factionRank
     * @param int         $balance
     * @param int         $factionRowId
     * @param string|null $lastFactionEdit
     */
    public function __construct(
        private string $xuid,
        private string $name,
        private FactionRank $factionRank,
        private int $balance = 0,
        private int $factionRowId = -1,
        private ?string $lastFactionEdit = null
    ) {
        $this->scoreboardBuilder = new ScoreboardBuilder($this, ScoreboardBuilder::SIDEBAR);
    }

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
     * @return ScoreboardBuilder
     */
    public function getScoreboardBuilder(): ScoreboardBuilder {
        return $this->scoreboardBuilder;
    }

    /**
     * @return FactionRank
     */
    public function getFactionRank(): FactionRank {
        return $this->factionRank;
    }

    /**
     * @param FactionRank|null $factionRank
     *
     * @return void
     */
    public function setFactionRank(FactionRank $factionRank = null): void {
        $this->factionRank = $factionRank ?? FactionRank::MEMBER();
    }

    /**
     * @return Faction|null
     */
    public function getFaction(): ?Faction {
        return FactionFactory::getInstance()->getFaction($this->factionRowId);
    }

    /**
     * @return Faction
     */
    public function getFactionNonNull(): Faction {
        return $this->getFaction() ?? throw new PluginException('Faction is null');
    }

    /**
     * @param Faction|null $faction
     */
    public function setFaction(?Faction $faction = null): void {
        $this->factionRowId = $faction === null ? -1 : $faction->getRowId();
    }

    /**
     * @return string|null
     */
    public function getLastFactionEdit(): ?string {
        return $this->lastFactionEdit;
    }

    /**
     * @param string|null $lastFactionEdit
     */
    public function setLastFactionEdit(?string $lastFactionEdit): void {
        $this->lastFactionEdit = $lastFactionEdit;
    }

    /**
     * @return int
     */
    public function getBalance(): int {
        return $this->balance;
    }

    /**
     * @param int $increase
     */
    public function increaseBalance(int $increase = 1): void {
        $this->balance += $increase;
    }

    /**
     * @param int $decrease
     */
    public function decreaseBalance(int $decrease = 1): void {
        $this->balance += $decrease;
    }

    /**
     * @param int $homeTeleport
     *
     * @return void
     */
    public function setHomeTeleport(int $homeTeleport): void {
        $this->homeTeleport = $homeTeleport;
    }

    /**
     * @return int
     */
    public function getHomeTeleport(): int {
        return $this->homeTeleport;
    }

    /**
     * @param ClaimZone|null $claimZone
     * @param string         $value
     */
    public function setClaimZone(?ClaimZone $claimZone, string $value = 'faction_claiming'): void {
        $this->claimZone = $claimZone;

        $item = VanillaItems::GOLDEN_HOE();

        if ($claimZone === null) {
            $this->getInstanceNonNull()->getInventory()->remove($item);

            return;
        }

        $nbt = $item->getNamedTag();
        $nbt->setString('custom_item', $value);

        $item->setNamedTag($nbt);

        $this->getInstanceNonNull()->getInventory()->addItem($item->setNamedTag($nbt)->setCustomName(TextFormat::colorize('&r&6&lClaim Tool')));
    }

    /**
     * @return ClaimZone|null
     */
    public function getClaimZone(): ?ClaimZone {
        return $this->claimZone;
    }

    /**
     * @return Player
     */
    public function getInstanceNonNull(): Player {
        return Server::getInstance()->getPlayerExact($this->name) ?? throw new PluginException('Player is offline');
    }

    public function save(): void {
        TaskUtils::runAsync(new SaveSessionAsync($this->xuid, $this->name, $this->factionRowId, $this->factionRank->ordinal(), 1, $this->balance));
    }
}