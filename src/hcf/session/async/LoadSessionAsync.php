<?php

declare(strict_types=1);

namespace hcf\session\async;

use hcf\task\QueryAsyncTask;
use hcf\utils\MySQL;
use mysqli_result;
use pocketmine\plugin\PluginException;

class LoadSessionAsync extends QueryAsyncTask {

    /**
     * @param string $xuid
     */
    public function __construct(
        private string $xuid
    ) {}

    /**
     * @param MySQL $mysqli
     */
    public function query(MySQL $mysqli): void {
        $mysqli->prepareStatement("SELECT * FROM players WHERE xuid = ?");
        $mysqli->set($this->xuid);

        $stmt = $mysqli->executeStatement();

        if (!($result = $stmt->get_result()) instanceof mysqli_result) {
            throw new PluginException($mysqli->error);
        }

        while ($fetch = $result->fetch_array(MYSQLI_ASSOC)) {
            $this->setResult($fetch);
        }

        $result->close();
        $stmt->close();

        /*if (!is_array($fetch = $result->fetch_array(MYSQLI_ASSOC))) {
            throw new PluginException($mysqli->error);
        }

        $result->close();
        $stmt->close();

        $this->setResult($fetch);*/
    }
}