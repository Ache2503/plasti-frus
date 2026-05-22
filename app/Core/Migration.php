<?php

namespace App\Core;

abstract class Migration
{
    protected Database $db;
    protected string $table;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    abstract public function up(): void;
    abstract public function down(): void;

    protected function schema(string $sql): void
    {
        $this->db->query($sql);
    }
}
