<?php

declare(strict_types=1);

namespace hcf\koth;

use hcf\faction\ClaimZone;
use hcf\HCF;
use hcf\Placeholders;
use hcf\session\SessionFactory;
use JsonException;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
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
    /** @var Player|null */
    private ?Player $target = null;
    /** @var int */
    private int $capturingTime = 0;

    public function init(): void {
        foreach ((new Config(HCF::getInstance()->getDataFolder() . 'koths.json'))->getAll() as $kothName => $data) {
            if (!is_array($data)) {
                continue;
            }

            $this->addKoth((string) $kothName, ClaimZone::deserialize(array_values($data['corners'])), $data['time']);
        }

        $this->findKoth();

        HCF::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            $this->tick();
        }), 20);
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
     */
    public function setKothName(string $kothName): void {
        $this->kothName = $kothName;

        $this->capturingTime = $this->kothsTime[$kothName];
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

    private function tick(): void {
        if (($kothName = $this->kothName) === null) {
            return;
        }

        if (($claimZone = $this->getKoth($kothName)) === null) {
            return;
        }

        if (($target = $this->target) === null || !$target->isConnected()) {
            foreach($claimZone->getWorld()->getNearbyEntities($claimZone->asAxisAligned()) as $targetEntity) {
                if (!$targetEntity instanceof Player) {
                    continue;
                }

                if (($session = SessionFactory::getInstance()->getSessionName($targetEntity->getName())) === null || $session->getFaction() === null) {
                    continue;
                }

                $this->target = $targetEntity;

                $targetEntity->sendMessage(Placeholders::replacePlaceholders('PLAYER_KOTH_CONTROLLING', $kothName));

                Server::getInstance()->broadcastMessage(Placeholders::replacePlaceholders('KOTH_SOMEONE_CONTROLLING', $kothName));

                break;
            }

            return;
        }

        $session = SessionFactory::getInstance()->getSessionName($target->getName());

        if (!$claimZone->isInside($target->getPosition()) ||
            $session === null ||
            $session->getFaction() === null
        ) {
            $target->sendMessage(Placeholders::replacePlaceholders('PLAYER_KOTH_CONTROLLING_LOST', $kothName));

            Server::getInstance()->broadcastMessage(Placeholders::replacePlaceholders('KOTH_CONTROLLING_LOST', $target->getName(), $kothName));

            $this->target = null;

            $this->capturingTime = $this->kothsTime[$kothName];

            return;
        }

        if ($this->kothName < 1) {
            Server::getInstance()->broadcastMessage(Placeholders::replacePlaceholders('KOTH_CAPTURING_END', $target->getName(), $session->getFactionNonNull()->getName(), $kothName));

            $this->findKoth($kothName);

            return;
        }

        $this->capturingTime--;
    }

    /**
     * @param string|null $except
     */
    private function findKoth(string $except = null): void {
        $koths = array_keys($this->koths);

        if ($except !== null) {
            $koths = array_filter($koths, function (string $kothName) use ($except): bool {
                return $kothName === $except;
            });
        }

        if (count($koths) === 0) {
            HCF::getInstance()->getLogger()->warning('Koth list is empty');

            return;
        }

        shuffle($koths);

        $this->setKothName($koths[array_key_first($koths)]);
    }
}