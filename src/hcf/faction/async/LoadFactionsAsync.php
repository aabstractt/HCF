<?php

declare(strict_types=1);

namespace hcf\faction\async;

use mysqli_result;
use hcf\task\QueryAsyncTask;
use hcf\utils\MySQL;

class LoadFactionsAsync extends QueryAsyncTask {

    /**
     * @param MySQL $mysqli
     */
    public function query(MySQL $mysqli): void {
        $stmt = $mysqli->executeStatement('SELECT * FROM player_factions');

        if (!($result = $stmt->get_result()) instanceof mysqli_result) {
            return;
        }

        $factionsResult = [];

        while (($data = $result->fetch_array(MYSQLI_ASSOC))) {
            $factionData = $data;

            $mysqli->prepareStatement("SELECT * FROM players WHERE factionRowId = ?");

            $mysqli->set($data['rowId']);

            $stmt0 = $mysqli->executeStatement();

            if (!($result0 = $stmt0->get_result()) instanceof mysqli_result) {
                continue;
            }

            while ($data0 = $result0->fetch_array(MYSQLI_ASSOC)) {
                $factionData['members'][] = $data0;
            }

            $stmt0->close();

            $mysqli->prepareStatement("SELECT * FROM faction_claims WHERE factionRowId = ?");
            $mysqli->set($data['rowId']);

            $stmt0 = $mysqli->executeStatement();

            if (!($result0 = $stmt0->get_result()) instanceof mysqli_result) {
                continue;
            }

            while ($data0 = $result0->fetch_array(MYSQLI_ASSOC)) {
                $factionData['claims'][$data0['rowId']] = $data0;
            }

            $factionsResult[] = $factionData;
        }

        $this->setResult($factionsResult);
    }
}