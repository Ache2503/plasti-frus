<?php
namespace App\Models;

use App\Core\Model;

class NotificacionCliente extends Model
{
    protected $table = 'notificaciones_cliente';
    protected $primaryKey = 'id_notificacion';

    public function getByCliente(int $idCliente, int $limite = 20): array
    {
        return $this->fetchAll("
            SELECT * FROM {$this->table}
            WHERE id_cliente = :id
            ORDER BY created_at DESC
            LIMIT " . (int) $limite,
            ['id' => $idCliente]
        );
    }

    public function unreadCount(int $idCliente): int
    {
        return (int) ($this->fetchOne("
            SELECT COUNT(*) as c FROM {$this->table}
            WHERE id_cliente = :id AND leida = 0
        ", ['id' => $idCliente])['c'] ?? 0);
    }

    public function markAsRead(int $idCliente): void
    {
        $this->db->query(
            "UPDATE {$this->table} SET leida = 1 WHERE id_cliente = :id AND leida = 0",
            ['id' => $idCliente]
        );
    }

    public function create(array $data): int
    {
        return $this->db->insert($this->table, [
            'id_cliente' => $data['id_cliente'],
            'tipo' => $data['tipo'] ?? 'info',
            'titulo' => $data['titulo'],
            'mensaje' => $data['mensaje'] ?? null,
            'id_referencia' => $data['id_referencia'] ?? null,
        ]);
    }
}
