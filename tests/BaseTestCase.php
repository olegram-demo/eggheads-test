<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use mysqli;
use RuntimeException;

class BaseTestCase extends TestCase
{
    private static bool $isInitialized = false;
    protected static mysqli $mysqli;

    protected function setUp(): void
    {
        parent::setUp();

        if (!static::$isInitialized) {
            static::$mysqli = $this->connect();
        }

        if (static::$mysqli->connect_error) {
            throw new \RuntimeException('mysqli connection error: ' . static::$mysqli->connect_error);
        }

        $this->dropTables();
    }

    private function connect(): mysqli
    {
        $mysqli = new mysqli('mysql', 'eggheads', 'eggheads', 'eggheads');
        static::$isInitialized = true;

        return $mysqli;
    }

    private function dropTables(): void
    {
        $this->query('SET foreign_key_checks = 0');
        if ($result = $this->query("SHOW TABLES")) {
            while ($row = $result->fetch_array(MYSQLI_NUM)) {
                $this->query('DROP TABLE IF EXISTS ' . $row[0]);
            }
        }
        $this->query('SET foreign_key_checks = 1');
    }

    protected function insert(string $table, array $data): void
    {
        $fieldsString = implode(',', \array_keys($data));
        $values = \array_map(static fn ($v) => "'$v'", \array_values($data));

        $valuesString = \implode(',', $values);

        $this->query("INSERT INTO $table ($fieldsString) VALUES ($valuesString)");
    }

    /**
     * @param string $sql
     * @return bool|\mysqli_result
     */
    protected function query(string $sql)
    {
        $result = static::$mysqli->query($sql);

        if (!$result) {
            throw new RuntimeException("Error executing sql query: " . static::$mysqli->error);
        }

        return $result;
    }
}
