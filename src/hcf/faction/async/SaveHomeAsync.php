<?php

declare(strict_types=1);

namespace hcf\faction\async;

use hcf\task\QueryAsyncTask;
use hcf\utils\MySQL;

class SaveHomeAsync extends QueryAsyncTask {

    /**
     * @param int    $factionRowId
     * @param string $homeString
     * @param bool   $create
     */
    public function __construct(
        private int $factionRowId,
        private string $homeString,
        private bool $create = false
    ) {}

    /**
     * @param MySQL $mysqli
     */
    public function query(MySQL $mysqli): void {
        if ($this->create) {
            $mysqli->prepareStatement("INSERT INTO faction_home (factionRowId, homeString) VALUES (?, ?)");

            $mysqli->set($this->factionRowId, $this->homeString);
        } else {
            $mysqli->prepareStatement("UPDATE faction_home SET homeString = ? WHERE factionRowId = ?");

            $mysqli->set($this->homeString);
        }

        $mysqli->executeStatement()->close();
    }
}