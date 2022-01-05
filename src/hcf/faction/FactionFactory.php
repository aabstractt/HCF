<?php

declare(strict_types=1);

namespace hcf\faction;

use hcf\faction\async\LoadFactionsAsync;
use hcf\faction\type\FactionMember;
use hcf\faction\type\FactionRank;
use hcf\faction\type\PlayerFaction;
use hcf\HCF;
use hcf\Placeholders;
use hcf\session\Session;
use hcf\TaskUtils;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\Position;

class FactionFactory {

    use SingletonTrait;

    /** @var int */
    public const STATUS_PAUSED = 0;
    public const STATUS_REGENERATING = 1;
    public const STATUS_FULL = 2;

    /** @var string[] */
    public static array $regenStatus = [
        self::STATUS_PAUSED => '{%0}',
        self::STATUS_REGENERATING => 'Regenerating',
        self::STATUS_FULL => 'Full'
    ];

    /** @var ClaimZone[] */
    private array $claims = [];
    /** @var array<string, int> */
    private array $factionNames = [];
    /** @var array<int, PlayerFaction> */
    private array $factions = [];

    public function init(): void {
        TaskUtils::runAsync(new LoadFactionsAsync(), function (LoadFactionsAsync $query): void {
            if (!is_array($result = $query->getResult()) || count($result) === 0) {
                HCF::getInstance()->getLogger()->warning('Factions is empty');

                return;
            }

            foreach ($result as $factionData) {
                $members = [];

                foreach ($factionData['members'] as $memberData) {
                    $members[$memberData['xuid']] = new FactionMember($memberData['xuid'], $memberData['name'], FactionRank::valueOf($memberData['rankId']));
                }

                $faction = new PlayerFaction(
                    $factionData['rowId'],
                    $factionData['name'],
                    $members,
                    $factionData['balance'],
                    $factionData['points'],
                    $factionData['deathsUntilRaidable'],
                    $factionData['regenCooldown'],
                    $factionData['lastDtrUpdate'],
                    $factionData['allies'] ?? [],
                    $factionData['requestedAllies'] ?? [],
                    $factionData['open'] === 1,
                    $factionData['friendlyFire'] === 1,
                    $factionData['lives'],
                    $factionData['announcement'] === '' ? null : $factionData['announcement']
                );

                $faction->findLeader();

                $this->factionNames[$faction->getName()] = $faction->getRowId();
                $this->factions[$faction->getRowId()] = $faction;

                foreach ($factionData['claims'] ?? [] as $claimData) {
                    $this->claims[$claimData['rowId']] = ClaimZone::deserialize($claimData);
                }
            }
        });
    }

    /**
     * @param Session          $session
     * @param PlayerFaction    $faction
     * @param FactionRank|null $factionRank
     *
     * @return void
     */
    public function joinFaction(Session $session, PlayerFaction $faction, FactionRank $factionRank = null): void {
        if ($factionRank === null) {
            $factionRank = FactionRank::MEMBER();
        }

        $session->setFaction($faction);
        $session->setFactionRank($factionRank);

        $session->save();

        $faction->broadcastMessage(Placeholders::replacePlaceholders('PLAYER_JOINED_FACTION', $session->getName()));

        $faction->addMember(FactionMember::valueOf($session->getXuid(), $session->getName(), $factionRank->ordinal()));

        if ($factionRank === FactionRank::MEMBER()) {
            # dtr-freeze = minutes
            $faction->setRemainingRegenerationTime(FactionFactory::getDtrFreeze() * 60);
        }

        if (!isset($this->factions[$faction->getRowId()])) {
            $faction->findLeader();

            $this->factions[$faction->getRowId()] = $faction;

            $this->factionNames[$faction->getName()] = $faction->getRowId();
        }

        $faction->save();
    }

    /**
     * @param Player $player
     *
     * @return PlayerFaction|null
     */
    public function getPlayerFaction(Player $player): ?PlayerFaction {
        $filter = array_filter($this->factions, fn(PlayerFaction $faction) => $faction->isMember($player->getXuid()));

        return $filter[array_key_first($filter)] ?? null;
    }

    /**
     * @param string $name
     *
     * @return PlayerFaction|null
     */
    public function getFactionName(string $name): ?PlayerFaction {
        return $this->factions[$this->factionNames[$name] ?? -1] ?? null;
    }

    /**
     * @param int $rowId
     *
     * @return Faction|null
     */
    public function getFaction(int $rowId): ?Faction {
        return $this->factions[$rowId] ?? null;
    }

    /**
     * @param Position $pos
     *
     * @return Faction|null
     */
    public function getFactionAt(Position $pos): ?Faction {
        foreach ($this->claims as $claim) {
            if (!$claim->isInside($pos)) {
                continue;
            }

            return $this->factions[$claim->getFactionRowId()] ?? null;
        }

        return null;
    }

    /**
     * @return int
     */
    final public static function getFactionNameMin(): int {
        return HCF::getInstance()->getInt('factions.min-name');
    }

    /**
     * @return int
     */
    final public static function getFactionNameMax(): int {
        return HCF::getInstance()->getInt('factions.max-name');
    }

    /**
     * @return int
     */
    final public static function getMaxMembers(): int {
        return HCF::getInstance()->getInt('factions.max-members');
    }

    /**
     * @return int
     */
    final public static function getMaxAllies(): int {
        return HCF::getInstance()->getInt('factions.max-allies');
    }

    /**
     * @return float
     */
    final public static function getMaxDtr(): float {
        return HCF::getInstance()->getFloat('factions.max-dtr');
    }

    /**
     * @return float
     */
    final public static function getDtrPerPlayer(): float {
        return HCF::getInstance()->getFloat('factions.dtr-per-player');
    }

    /**
     * @return int
     */
    final public static function getDtrUpdate(): int {
        return HCF::getInstance()->getInt('factions.dtr-regen-time');
    }

    /**
     * @return int
     */
    final public static function getDtrFreeze(): int {
        return HCF::getInstance()->getInt('factions.dtr-freeze');
    }

    /**
     * @return float
     */
    final public static function getDtrIncrementBetweenUpdate(): float {
        return HCF::getInstance()->getFloat('factions.dtr-increment');
    }
}