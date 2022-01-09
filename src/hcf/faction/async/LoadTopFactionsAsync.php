<?php

declare(strict_types=1);

namespace hcf\faction\async;

use hcf\task\QueryAsyncTask;
use hcf\utils\MySQL;
use mysqli_result;
use pocketmine\plugin\PluginException;

class LoadTopFactionsAsync extends QueryAsyncTask {

    /**
     * @param MySQL $mysqli
     */
    public function query(MySQL $mysqli): void {
        $stmt = $mysqli->executeStatement("SELECT * FROM player_factions ORDER BY points DESC");

        if (!($result = $stmt->get_result()) instanceof mysqli_result) {
            throw new PluginException($mysqli->error);
        }

        $fetch = [];
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $fetch[] = $row;
        }

        $this->setResult($fetch);
    }
}