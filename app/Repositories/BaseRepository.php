<?php
namespace App\Repositories;

use App\Core\Database;

abstract class BaseRepository
{
    protected Database $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} ORDER BY {$this->primaryKey} DESC"
        );
    }

    public function find($id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id",
            ['id' => $id]
        );
    }

    public function create(array $data): int
    {
        return $this->db->insert($this->table, $data);
    }

    public function update($id, array $data): int
    {
        return $this->db->update(
            $this->table,
            $data,
            "{$this->primaryKey} = :primaryKey",
            ['primaryKey' => $id]
        );
    }

    public function delete($id): int
    {
        return $this->db->delete(
            $this->table,
            "{$this->primaryKey} = :id",
            ['id' => $id]
        );
    }

    public function where(string $column, $value, string $operator = '='): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE {$column} {$operator} :value",
            ['value' => $value]
        );
    }

    public function whereFirst(string $column, $value, string $operator = '='): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE {$column} {$operator} :value LIMIT 1",
            ['value' => $value]
        );
    }

    public function count(): int
    {
        $result = $this->db->fetchOne("SELECT COUNT(*) as total FROM {$this->table}");
        return (int) ($result['total'] ?? 0);
    }

    public function paginate(int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $data = $this->db->fetchAll(
            "SELECT * FROM {$this->table} ORDER BY {$this->primaryKey} DESC LIMIT :limit OFFSET :offset",
            ['limit' => $perPage, 'offset' => $offset]
        );
        $total = $this->count();
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int) ceil($total / $perPage),
        ];
    }
}
