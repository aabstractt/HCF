<?php

declare(strict_types=1);

namespace hcf\session\scoreboard;

use hcf\faction\type\koth\KothFactory;
use hcf\Placeholders;
use hcf\session\Session;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\utils\TextFormat;

class ScoreboardBuilder {

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
     * @param string  $title
     * @param string  $displaySlot
     * @param array   $scoreboardLines
     * @param int     $sortOrder
     */
    public function __construct(
        private Session $session,
        private string $title,
        private string $displaySlot,
        private array $scoreboardLines,
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

        foreach ($this->scoreboardLines['default'] ?? [] as $line) {
            if (!is_string($str = str_replace('%', '', $line))) {
                continue;
            }

            $data = $this->scoreboardLines[$str] ?? [];

            if (count($data) === 0) {
                $lines[] = $line;

                continue;
            }

            $lines = array_merge($lines, $data);
        }

        $this->removeLines();

        $slot = 1;
        foreach ($lines as $line) {
            if (($newLine = self::replacePlaceHolders($line)) === null) {
                continue;
            }

            $this->setLine($slot++, $newLine);
        }
    }

    /**
     * @param string $text
     *
     * @return string|null
     */
    private static function replacePlaceHolders(string $text): ?string {
        $placeholders = [
            '%koth-enabled%' => ($kothName = KothFactory::getInstance()->getKothName()) !== null,
            '%koth-name%' => ($kothName ?? ''),
            '%koth-time%' => Placeholders::timeString(KothFactory::getInstance()->getCapturingTime()),
            '%spawntag-enabled%' => true,
            '%spawntag-time%' => Placeholders::timeString(30)
        ];

        foreach ($placeholders as $search => $replace) {
            $replace = strval($replace);

            $text = str_replace($search, $replace, $text);
        }

        return self::shouldDisplay($text);
    }

    private static function shouldDisplay(string $text): ?string {
        if (!str_contains($text, '<display=')) {
            return $text;
        }

        $split = explode("<display=", $text);

        return $split[1] === '1' ? $split[0] : null;
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