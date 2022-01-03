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
        $serialized = (array) unserialize($this->serialized);

        if (($rowId = $serialized['rowId'] ?? -1) === -1) {
            $mysqli->prepareStatement("INSERT INTO player_factions (name, dtr) VALUES (?, ?)");
            $mysqli->set(...$serialized);

            $stmt = $mysqli->executeStatement();

            $this->setResult($stmt->insert_id);

            $stmt->close();

            return;
        }

        unset($serialized['rowId']);

        $mysqli->prepareStatement("UPDATE player_factions SET name = ?, lastRegen = ?, dtr = ?, startRegen = ?, lastRegen = ?, regenerating = ?, open = ?, friendlyFire = ?, lives = ?, balance = ?, points = ?, announcement = ? WHERE rowId = ?");

        $mysqli->set(...$serialized);
        $mysqli->set($rowId);

        $mysqli->executeStatement()->close();
    }
}