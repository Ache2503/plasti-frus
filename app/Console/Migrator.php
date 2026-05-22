<?php

namespace App\Console;

use App\Core\Database;

class Migrator
{
    private Database $db;
    private string $migrationsPath;
    private string $table = 'migrations';

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->migrationsPath = dirname(__DIR__, 2) . '/database/migrations';
        $this->ensureMigrationTable();
    }

    public function run(): void
    {
        $executed = $this->getExecutedMigrations();
        $files = glob($this->migrationsPath . '/*.php');
        sort($files);

        foreach ($files as $file) {
            $filename = basename($file);
            if (in_array($filename, $executed)) {
                continue;
            }

            require_once $file;
            $class = $this->getMigrationClass($filename);
            if (class_exists($class)) {
                (new $class())->up();
                $this->markAsExecuted($filename);
                echo "Migrated: {$filename}\n";
            }
        }
    }

    public function rollback(): void
    {
        $executed = $this->getExecutedMigrations();
        $last = array_pop($executed);
        if (!$last) {
            echo "Nothing to rollback.\n";
            return;
        }

        $file = $this->migrationsPath . '/' . $last;
        require_once $file;
        $class = $this->getMigrationClass($last);
        if (class_exists($class)) {
            (new $class())->down();
            $this->db->delete($this->table, 'migration = :m', ['m' => $last]);
            echo "Rolled back: {$last}\n";
        }
    }

    public function reset(): void
    {
        $executed = array_reverse($this->getExecutedMigrations());
        foreach ($executed as $migration) {
            $file = $this->migrationsPath . '/' . $migration;
            require_once $file;
            $class = $this->getMigrationClass($migration);
            if (class_exists($class)) {
                (new $class())->down();
                $this->db->delete($this->table, 'migration = :m', ['m' => $migration]);
                echo "Rolled back: {$migration}\n";
            }
        }
    }

    private function ensureMigrationTable(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS {$this->table} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    private function getExecutedMigrations(): array
    {
        $rows = $this->db->fetchAll("SELECT migration FROM {$this->table} ORDER BY id");
        return array_column($rows, 'migration');
    }

    private function markAsExecuted(string $name): void
    {
        $this->db->insert($this->table, ['migration' => $name]);
    }

    private function getMigrationClass(string $filename): string
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $parts = explode('_', $name);
        $parts = array_slice($parts, 4);
        $className = '';
        foreach ($parts as $p) {
            $className .= ucfirst($p);
        }
        return "App\\Database\\Migrations\\{$className}";
    }
}
