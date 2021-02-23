<?php

declare(strict_types=1);

namespace Tests;

class TaskNumber1Test extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function testSqlInjection(): void
    {
        $id = '-1 UNION SELECT group_concat(id, secret) FROM secrets;';
        $res = static::$mysqli->query("SELECT * FROM users WHERE id=$id");
        $user = $res->fetch_assoc();
        $res->free();

        static::assertEquals('1secret', $user['id'] ?? '');
    }

    private function seed(): void
    {
        $this->createTables();
        $this->insert('users', []);
        $this->insert('secrets', ['secret' => 'secret']);
    }

    private function createTables(): void
    {
        $this->query('CREATE TABLE users (id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY);');
        $this->query('CREATE TABLE secrets (id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, secret varchar(255));');
    }
}
