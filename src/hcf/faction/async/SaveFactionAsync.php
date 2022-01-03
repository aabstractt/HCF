<?php

declare(strict_types=1);

namespace hcf\faction\async;

use hcf\task\QueryAsyncTask;
use hcf\utils\MySQL;

class SaveFactionAsync extends QueryAsyncTask {

    /**
     * @param string $serialized
     */
    public function __construct(
        private string $serialized
    ) {}

    /**
     * @param MySQL $mysqli
     */
    public function query(MySQL $mysqli): void {
        $s = (array) unserialize($this->serialized);

        if (($s['rowId'] ?? -1) === -1) {
            $mysqli->prepareStatement("INSERT INTO player_factions (name, deathsUntilRaidable) VALUES (?, ?)");
            $mysqli->set(...$s);

            $stmt = $mysqli->executeStatement();

            $this->setResult($stmt->insert_id);

            $stmt->close();

            return;
        }

        $mysqli->prepareStatement("UPDATE player_factions SET name = ?, lastRename = ?, deathsUntilRaidable = ?, regenCooldown = ?, lastDtrUpdate = ?, open = ?, friendlyFire = ?, lives = ?, balance = ?, points = ?, announcement = ? WHERE rowId = ?");

        $mysqli->set($s['name'], $s['lastRename'] ?? '', $s['deathsUntilRaidable'], $s['regenCooldown'], $s['lastDtrUpdate'], $s['open'], $s['friendlyFire'], $s['lives'], $s['balance'], $s['points'], $s['announcement'], $s['rowId']);

        $mysqli->executeStatement()->close();
    }
}