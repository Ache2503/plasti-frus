<?php
namespace Database\Seeds;

use App\Core\Database;

abstract class Seeder
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    abstract public function run(): void;

    protected function truncate(string $table): void
    {
        $conn = $this->db->getConnection();
        $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
        $conn->exec("TRUNCATE TABLE {$table}");
        $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
    }

    protected function insert(string $table, array $data): int
    {
        return $this->db->insert($table, $data);
    }
}
