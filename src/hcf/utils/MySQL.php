<?php

declare(strict_types=1);

namespace hcf\utils;

use mysqli;
use mysqli_stmt;
use RuntimeException;

class MySQL extends mysqli {

    /** @var string */
    private string $queryPrepare;
    /** @var array */
    private array $replace = [];

    /**
     * @param string $query
     */
    public function prepareStatement(string $query): void {
        $this->queryPrepare = $query;
    }

    /**
     * @param string|null $query
     *
     * @return mysqli_stmt
     */
    public function executeStatement(string $query = null): mysqli_stmt {
        if ($query === null) {
            $query = $this->statement();
        }

        $stmt = $this->prepare(str_replace('empty', 'null', $query));

        if (!$stmt instanceof mysqli_stmt || !$stmt->execute()) {
            throw new RuntimeException($this->error);
        }

        $this->replace = [];

        return $stmt;
    }

    // This allows us to add a null value without problems with the sql query
    public function null(): void {
        $this->replace[] = "empty";
    }

    /**
     * @param mixed ...$values
     */
    public function set(mixed... $values): void {
        foreach ($values as $value) {
            if (is_bool($value)) {
                $value = $value ? 1 : 0;
            }

            $this->replace[] = "'" . $value . "'";
        }
    }

    /**
     * @return string
     */
    private function statement(): string {
        return preg_replace(array_fill(0, count($this->replace), "/\?/"), $this->replace, $this->queryPrepare, 1) ?? '';
    }
}