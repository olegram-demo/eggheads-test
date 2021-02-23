<?php

declare(strict_types=1);

namespace Tests;

class TaskNumber3Test extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function testQuery(): void
    {
        $sql = <<<SQL
SELECT u.name, u.phone, COUNT(o.id), AVG(o.subtotal), MAX(o.created_at)
FROM users u
LEFT JOIN orders o
ON o.user_id = u.id
GROUP BY u.id
SQL;
        $expectedResult = [
            ['user1', 'phone1', '0', null, null],
            ['user2', 'phone2', '2', '1050.0000', '2019-02-11 10:00:00'],
            ['user3', 'phone3', '4', '2150.0000', '2020-04-13 13:00:00'],
        ];

        $result = static::$mysqli->query($sql)->fetch_all();

        static::assertEquals($expectedResult, $result);
    }

    private function seed(): void
    {
        $this->createTables();

        for ($u = 0; $u < 3; $u++) {
            $this->insert('users', [
                'name' => 'user' . ($u + 1),
                'phone' => 'phone' . ($u + 1),
                'email' => 'email' . ($u + 1),
                'created_at' => date("Y-m-d H:i:s"),
            ]);

            for ($o = 0; $o < $u * 2; $o++) {
                $this->insert('orders', [
                    'subtotal' => $u * 1000 + $o * 100,
                    'created_at' => (2018 + $u) . '-' . (1 + $o) . '-' . (10 + $o) . ' ' . (8 + $u + $o) . ':00:00',
                    'city_id' => $u * $o + $o + 1,
                    'user_id' => $u + 1,
                ]);
            }
        }
    }

    private function createTables(): void
    {
        $sql = <<<SQL
CREATE TABLE users (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    phone VARCHAR(255),
    email VARCHAR(255),
    created_at DATETIME
);
SQL;
        $this->query($sql);

        $sql = <<<SQL
CREATE TABLE orders (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subtotal SMALLINT UNSIGNED,
    created_at DATETIME,
    city_id TINYINT UNSIGNED,
    user_id TINYINT UNSIGNED
);
SQL;
        $this->query($sql);
    }
}
