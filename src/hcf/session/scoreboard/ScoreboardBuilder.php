<?php

declare(strict_types=1);

namespace hcf\session\scoreboard;

use hcf\session\Session;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\utils\TextFormat;

class ScoreboardBuilder {

    private string $title = 'HCF';

    /** @var string */
    public const LIST = 'list';
    public const SIDEBAR = 'sidebar';

    /** @var int */
    public const ASCENDING = 0;
    public const DESCENDING = 1;

    /** @var string */
    private string $objectiveName;

    /**
     * @param Session $session
     * @param string  $displaySlot
     * @param int     $sortOrder
     */
    public function __construct(
        private Session $session,
        private string $displaySlot,
        private int $sortOrder = self::ASCENDING
    ) {
        $this->objectiveName = uniqid('', true);
    }

    public function update(): void {
        $instance = $this->session->getInstanceNonNull();

        if (!$instance->isOnline()) {
            return;
        }

        $lines = [];
        $space = '&7&m------------------';

        $this->addLine($lines, '&a', '&6' . date('d/m/Y H:i'), '&b' . $space, '&7serverip.com');

        $this->removeLines();

        foreach ($lines as $slot => $line) {
            $this->setLine($slot, $line);
        }
    }

    private function addLine(array &$currentLines, string... $lines): void {
        foreach ($lines as $line) {
            $currentLines[] = TextFormat::colorize($line);
        }
    }

    public function removePlayer(): void {
        $this->session->getInstanceNonNull()->getNetworkSession()->sendDataPacket(RemoveObjectivePacket::create($this->objectiveName));
    }

    public function addPlayer(): void {
        $this->session->getInstanceNonNull()->getNetworkSession()->sendDataPacket(SetDisplayObjectivePacket::create(
            $this->displaySlot,
            $this->objectiveName,
            $this->title,
            'dummy',
            $this->sortOrder
        ));
    }

    private function removeLines(): void {
        for ($i = 0; $i <= 15; $i++) {
            $this->session->getInstanceNonNull()->getNetworkSession()->sendDataPacket($this->getPackets($i, '', SetScorePacket::TYPE_REMOVE));
        }
    }

    /**
     * @param int    $slot
     * @param string $line
     */
    public function setLine(int $slot, string $line): void {
        $instance = $this->session->getInstanceNonNull();

        $instance->getNetworkSession()->sendDataPacket($this->getPackets($slot, $line, SetScorePacket::TYPE_REMOVE));
        $instance->getNetworkSession()->sendDataPacket($this->getPackets($slot, $line, SetScorePacket::TYPE_CHANGE));
    }

    /**
     * @param int    $slot
     * @param string $message
     * @param int    $type
     *
     * @return SetScorePacket
     */
    public function getPackets(int $slot, string $message, int $type): SetScorePacket {
        $entry = new ScorePacketEntry();
        $entry->objectiveName = $this->objectiveName;
        $entry->score = $slot;
        $entry->scoreboardId = $slot;

        if ($type === SetScorePacket::TYPE_CHANGE) {
            if ($message == "") {
                $message = str_repeat(' ', $slot - 1);
            }

            $entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;

            $entry->customName = TextFormat::colorize($message) . ' ';
        }

        return SetScorePacket::create($type, [$entry]);
    }
}