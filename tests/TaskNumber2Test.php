<?php

declare(strict_types=1);

namespace Tests;

class TaskNumber2Test extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function testNPlus1Problem(): void
    {
        $expectedResult = [
            [
                'question' => [
                    'id' => '3',
                    'catalog_id' => '2',
                    'user_id' => '1',
                ],
                'user' => [
                    'name' => 'user1',
                    'gender' => '0',
                ],
            ],
            [
                'question' => [
                    'id' => '4',
                    'catalog_id' => '2',
                    'user_id' => '3',
                ],
                'user' => [
                    'name' => 'user3',
                    'gender' => '2',
                ],
            ]
        ];

        // source
        $catId = 2;
        $questionsQ = static::$mysqli->query('SELECT * FROM questions WHERE catalog_id=' . $catId);
        $result = [];
        while ($question = $questionsQ->fetch_assoc()) {
            $userQ = static::$mysqli->query('SELECT name, gender FROM users WHERE id=' . $question['user_id']);
            $user = $userQ->fetch_assoc();
            $result[] = ['question' => $question, 'user' => $user];
            $userQ->free();
        }
        $questionsQ->free();

        static::assertEquals($expectedResult, $result);

        // optimization
        $sql = <<<SQL
SELECT q.id q_id, q.catalog_id, u.id, u.name, u.gender
FROM questions q
LEFT JOIN users u
ON q.user_id = u.id
WHERE catalog_id = ?
SQL;
        $result = [];
        $stmt = static::$mysqli->prepare($sql);
        $stmt->bind_param('i', $catId);
        $stmt->execute();
        $query = $stmt->get_result();
        while ($row = $query->fetch_assoc()) {
            $result[] = [
                'question' => [
                    'id' => $row['q_id'],
                    'catalog_id' => $row['catalog_id'],
                    'user_id' => $row['id'],
                ],
                'user' => [
                    'name' => $row['name'],
                    'gender' => $row['gender'],
                ],
            ];
        }
        $query->free();

        static::assertEquals($expectedResult, $result);
    }

    private function seed(): void
    {
        $this->createTables();

        for ($u = 0; $u < 3; $u++) {
            $this->insert('users', [
                'name' => 'user' . ($u + 1),
                'gender' => $u,
            ]);
        }

        for ($c = 0; $c < 2; $c++) {
            for ($q = 0; $q < 2; $q++) {
                $this->insert('questions', [
                    'catalog_id' => $c + 1,
                    'user_id' => $c * $q + $q + 1,
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
    gender TINYINT UNSIGNED
);
SQL;
        $this->query($sql);

        $sql = <<<SQL
CREATE TABLE questions (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    catalog_id TINYINT UNSIGNED,
    user_id TINYINT UNSIGNED
);
SQL;
        $this->query($sql);
    }
}
