<?php

declare(strict_types=1);

namespace hcf\faction;

use hcf\faction\async\DeleteFactionAsync;
use hcf\faction\async\LoadFactionsAsync;
use hcf\faction\type\FactionMember;
use hcf\faction\type\FactionRank;
use hcf\faction\type\PlayerFaction;
use hcf\faction\type\RoadFaction;
use hcf\faction\type\SafezoneFaction;
use hcf\HCF;
use hcf\Placeholders;
use hcf\session\async\SaveSessionAsync;
use hcf\session\Session;
use hcf\session\SessionFactory;
use hcf\task\QueryAsyncTask;
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
    /** @var array<int, Faction> */
    private array $factions = [];

    public function init(): void {
        TaskUtils::runAsync(new LoadFactionsAsync(), function (QueryAsyncTask $query): void {
            if (!is_array($result = $query->getResult()) || count($result) === 0) {
                HCF::getInstance()->getLogger()->warning('Factions is empty');

                return;
            }

            foreach ($result as $factionData) {
                $members = [];

                foreach ($factionData['members'] as $memberData) {
                    $members[$memberData['xuid']] = new FactionMember($memberData['xuid'], $memberData['name'], FactionRank::valueOf($memberData['rankId']));
                }

                if (in_array($factionData['name'], HCF::getInstance()->getArray('factions.invalid-names'), true)) {
                    $faction = self::checkFaction($factionData['rowId'], $factionData['name']);
                } else {
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
                }

                if (isset($factionData['homeString'])) {
                    $faction->setHomePosition(Placeholders::stringToLocation($factionData['homeString']));
                }

                foreach ($factionData['claims'] ?? [] as $claimData) {
                    $this->claims[$faction->getRowId()] = ($claimZone = ClaimZone::deserialize($claimData));

                    $faction->setClaimZone($claimZone);
                }

                $this->factionNames[$faction->getName()] = $faction->getRowId();
                $this->factions[$faction->getRowId()] = $faction;
            }
        });
    }

    /**
     * @param Session          $session
     * @param Faction    $faction
     * @param FactionRank|null $factionRank
     *
     * @return void
     */
    public function joinFaction(Session $session, Faction $faction, FactionRank $factionRank = null): void {
        if ($factionRank === null) {
            $factionRank = FactionRank::MEMBER();
        }

        $session->setFaction($faction);
        $session->setFactionRank($factionRank);

        $session->save();

        $faction->addMember(FactionMember::valueOf($session->getXuid(), $session->getName(), $factionRank->ordinal()));
        $faction->broadcastMessage(Placeholders::replacePlaceholders('PLAYER_JOINED_FACTION', $session->getName()));

        if ($factionRank === FactionRank::MEMBER() && $faction instanceof PlayerFaction) {
            # dtr-freeze = minutes
            $faction->setRemainingRegenerationTime(FactionFactory::getDtrFreeze() * 60);
        }

        if (!isset($this->factions[$faction->getRowId()])) {
            if ($faction instanceof PlayerFaction) {
                $faction->findLeader();
            }

            $this->factions[$faction->getRowId()] = $faction;

            $this->factionNames[$faction->getName()] = $faction->getRowId();
        }

        $faction->save();
    }

    /**
     * @param Faction $faction
     *
     * @return void
     */
    public function disbandFaction(Faction $faction): void {
        foreach ($faction->getMembers() as $member) {
            if (($session = SessionFactory::getInstance()->getSessionName($member->getName())) === null) {
                TaskUtils::runAsync(new SaveSessionAsync($member->getXuid(), $member->getName(), -1, 0, 0, -1));

                continue;
            }

            $session->setFaction();
            $session->setFactionRank();

            $session->save();
        }

        TaskUtils::runAsync(new DeleteFactionAsync($faction->getRowId()));

        unset($this->factions[$faction->getRowId()], $this->factionNames[$faction->getName()]);
    }

    /**
     * @param Player $player
     *
     * @return PlayerFaction|null
     */
    public function getPlayerFaction(Player $player): ?PlayerFaction {
        $filter = array_filter($this->factions, fn(Faction $faction) => $faction instanceof PlayerFaction && $faction->isMember($player->getXuid()));

        return $filter[array_key_first($filter)] ?? null;
    }

    /**
     * @param string $name
     *
     * @return PlayerFaction|null
     */
    public function getFactionName(string $name): ?PlayerFaction {
        /** @var $faction PlayerFaction|null */
        if (!($faction = $this->getServerFaction($name)) instanceof PlayerFaction) {
            return null;
        }

        return $faction;
    }

    /**
     * @param string $name
     *
     * @return Faction|null
     */
    public function getServerFaction(string $name): ?Faction {
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
            if (!$claim->isInside($pos, false)) {
                continue;
            }

            return $this->factions[$claim->getFactionRowId()] ?? null;
        }

        return null;
    }

    /**
     * @param ClaimZone $claimZone
     */
    public function addClaim(ClaimZone $claimZone): void {
        $this->claims[$claimZone->getFactionRowId()] = $claimZone;
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

    /**
     * @param int    $rowId
     * @param string $name
     *
     * @return Faction
     */
    final public static function checkFaction(int $rowId, string $name): Faction {
        return str_contains($name, 'Road') ? new RoadFaction($rowId, $name) : new SafezoneFaction($rowId, $name);
    }
}