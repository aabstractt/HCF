<?php

declare(strict_types=1);

namespace hcf\faction\async;

use hcf\task\QueryAsyncTask;
use hcf\utils\MySQL;

class AddClaimAsync extends QueryAsyncTask {

    /**
     * @param int    $factionRowId
     * @param string $firstCorner
     * @param string $secondCorner
     */
    public function __construct(
        private int $factionRowId,
        private string $firstCorner,
        private string $secondCorner
    ) {}

    /**
     * @param MySQL $mysqli
     */
    public function query(MySQL $mysqli): void {
        $mysqli->prepareStatement("INSERT INTO faction_claims (factionRowId, firstCorner, secondCorner) VALUES (?, ?, ?)");
        $mysqli->set($this->factionRowId, $this->firstCorner, $this->secondCorner);

        $stmt = $mysqli->executeStatement();

        $this->setResult($stmt->insert_id);

        $stmt->close();
    }
}