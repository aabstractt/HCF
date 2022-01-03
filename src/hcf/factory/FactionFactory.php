<?php

declare(strict_types=1);

namespace hcf\factory;

use hcf\faction\async\LoadFactionsAsync;
use hcf\faction\ClaimZone;
use hcf\faction\Faction;
use hcf\faction\type\FactionMember;
use hcf\faction\type\FactionRank;
use hcf\faction\type\PlayerFaction;
use hcf\HCF;
use hcf\TaskUtils;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\Position;

class FactionFactory {

    use SingletonTrait;

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
                    $factionData['dtr'],
                    $factionData['startRegen'] === '' ? null : $factionData['startRegen'],
                    $factionData['lastRegen'] === '' ? null : $factionData['lastRegen'],
                    $factionData['regenerating'] === 1,
                    $factionData['allies'] ?? [],
                    $factionData['requestedAllies'] ?? [],
                    $factionData['invited'] ?? [],
                    $factionData['open'] === 1,
                    $factionData['friendlyFire'] === 1,
                    $factionData['lives'],
                    $factionData['announcement'] === '' ? null : $factionData['announcement']
                );

                $faction->findLeader();

                $this->factionNames[$faction->getName()] = $faction->getRowId();
                $this->factions[$faction->getRowId()] = $faction;

                foreach ($factionData['claims'] as $claimData) {
                    $this->claims[$claimData['rowId']] = ClaimZone::deserialize($claimData);
                }
            }
        });
    }

    /**
     * @param string $name
     *
     * @return PlayerFaction|null
     */
    public function getPlayerFaction(string $name): ?PlayerFaction {
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
    public static function getFactionNameMin(): int {
        return is_int($value = HCF::getInstance()->getConfig()->getNested('faction.name-min', 3)) ? $value : 3;
    }

    /**
     * @return int
     */
    public static function getFactionNameMax(): int {
        return is_int($value = HCF::getInstance()->getConfig()->getNested('faction.name-max', 16)) ? $value : 16;
    }
}