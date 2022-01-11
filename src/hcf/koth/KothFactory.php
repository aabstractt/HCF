<?php

declare(strict_types=1);

namespace hcf\koth;

use hcf\faction\ClaimZone;
use hcf\HCF;
use hcf\Placeholders;
use JsonException;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;

class KothFactory {

    use SingletonTrait;

    /** @var array<string, ClaimZone> */
    private array $koths = [];
    /** @var array<string, int> */
    private array $kothsTime = [];
    /** @var string|null */
    private ?string $kothName = null;

    public function init(): void {
        foreach ((new Config(HCF::getInstance()->getDataFolder() . 'koths.json'))->getAll() as $kothName => $data) {
            if (!is_array($data)) {
                continue;
            }

            $this->addKoth((string) $kothName, ClaimZone::deserialize(array_values($data['corners'])), $data['time']);
        }
    }

    /**
     * @return string|null
     */
    public function getKothName(): ?string {
        return $this->kothName;
    }

    /**
     * @param string    $kothName
     * @param ClaimZone $claimZone
     * @param int       $time
     */
    public function addKoth(string $kothName, ClaimZone $claimZone, int $time = -1): void {
        $this->koths[$kothName] = $claimZone;

        if ($time === -1) {
            $this->saveKoths($kothName, [
                'corners' => [Placeholders::locationToString($claimZone->getFirsCorner()), Placeholders::locationToString($claimZone->getSecondCorner())],
                'time' => ($time = 600)
            ]);
        }

        $this->kothsTime[$kothName] = $time;
    }

    /**
     * @param string $kothName
     * @param int    $time
     */
    public function setKothTime(string $kothName, int $time): void {
        $this->kothsTime[$kothName] = $time;
    }

    /**
     * @param string $kothName
     *
     * @return ClaimZone|null
     */
    public function getKoth(string $kothName): ?ClaimZone {
        return $this->koths[$kothName] ?? null;
    }

    /**
     * @param string $kothName
     * @param array  $data
     */
    public function saveKoths(string $kothName, array $data): void {
        $config = new Config(HCF::getInstance()->getDataFolder() . 'koths.json');

        try {
            $config->set($kothName, array_merge((array) $config->get($kothName, []), $data));
            $config->save();
        } catch (JsonException $e) {
            HCF::getInstance()->getLogger()->logException($e);
        }
    }

    /**
     * @return ClaimZone[]
     */
    public function getKoths(): array {
        return $this->koths;
    }
}