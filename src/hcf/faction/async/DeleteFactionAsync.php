<?php

declare(strict_types=1);

namespace hcf\faction\async;

use hcf\task\QueryAsyncTask;
use hcf\utils\MySQL;

class DeleteFactionAsync extends QueryAsyncTask {

    /**
     * @param int $rowId
     */
    public function __construct(
        private int $rowId
    ) {}

    /**
     * @param MySQL $mysqli
     */
    public function query(MySQL $mysqli): void {
        // TODO: Implement query() method.
    }
}