<?php

declare(strict_types=1);

namespace hcf\session\async;

use hcf\utils\MySQL;
use mysqli_result;
use pocketmine\plugin\PluginException;

class SaveSessionAsync extends LoadSessionAsync {

    /**
     * @param string $xuid
     * @param string $name
     * @param int    $factionRowId
     * @param int    $rankId
     * @param int    $lives
     * @param int    $balance
     */
    public function __construct(
        private string $xuid,
        private string $name,
        private int $factionRowId,
        private int $rankId,
        private int $lives,
        private int $balance
    ) {
        parent::__construct($this->xuid);
    }

    /**
     * @param MySQL $mysqli
     */
    public function query(MySQL $mysqli): void {
        if ($this->balance === -1) {
            self::query($mysqli);

            if (!is_array($fetch = $this->getResult())) {
                throw new PluginException('Player not found');
            }

            $this->balance = $fetch['balance'];
        }

        $mysqli->prepareStatement("SELECT * FROM players WHERE xuid = ?");
        $mysqli->set($this->xuid);

        $stmt = $mysqli->executeStatement();

        if (!($result = $stmt->get_result()) instanceof mysqli_result) {
            throw new PluginException($mysqli->error);
        }

        $exists = is_array($fetch = $result->fetch_array(MYSQLI_ASSOC)) && count($fetch) > 0;

        $result->close();
        $stmt->close();

        if (!$exists) {
            $mysqli->prepareStatement("INSERT INTO players (name, xuid, lives, balance, factionRowId, rankId) VALUES (?, ?, ?, ?, ?, ?)");
            $mysqli->set($this->name, $this->xuid, $this->lives, $this->balance, $this->factionRowId, $this->rankId);

            $mysqli->executeStatement()->close();

            return;
        }

        $mysqli->prepareStatement("UPDATE players SET name = ?, lives = ?, balance = ?, factionRowId = ?, rankId = ? WHERE xuid = ?");
        $mysqli->set($this->name, $this->lives, $this->balance, $this->factionRowId, $this->rankId, $this->xuid);

        $mysqli->executeStatement()->close();
    }
}