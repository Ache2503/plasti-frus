<?php
namespace App\Services;

use App\Core\Database;

class NotificacionService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function vendedorNotify(int $idVendedor, string $tipo, string $titulo, ?string $mensaje = null, ?int $idReferencia = null): void
    {
        $this->db->insert('notificaciones_vendedor', [
            'id_vendedor' => $idVendedor,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'id_referencia' => $idReferencia,
        ]);
    }

    public function vendedorNotifications(int $idVendedor, int $limite = 10): array
    {
        return $this->db->fetchAll("
            SELECT * FROM notificaciones_vendedor
            WHERE id_vendedor = :id
            ORDER BY created_at DESC LIMIT " . (int) $limite,
            ['id' => $idVendedor]
        );
    }

    public function vendedorUnreadCount(int $idVendedor): int
    {
        return (int) ($this->db->fetchOne("
            SELECT COUNT(*) as c FROM notificaciones_vendedor
            WHERE id_vendedor = :id AND leida = 0
        ", ['id' => $idVendedor])['c'] ?? 0);
    }

    public function operadorNotify(int $idOperador, string $tipo, string $titulo, ?string $mensaje = null, ?int $idReferencia = null): void
    {
        $this->db->insert('notificaciones_operador', [
            'id_operador' => $idOperador,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'id_referencia' => $idReferencia,
        ]);
    }

    public function operadorNotifications(int $idOperador, int $limite = 10): array
    {
        return $this->db->fetchAll("
            SELECT * FROM notificaciones_operador
            WHERE id_operador = :id
            ORDER BY created_at DESC LIMIT " . (int) $limite,
            ['id' => $idOperador]
        );
    }

    public function operadorUnreadCount(int $idOperador): int
    {
        return (int) ($this->db->fetchOne("
            SELECT COUNT(*) as c FROM notificaciones_operador
            WHERE id_operador = :id AND leida = 0
        ", ['id' => $idOperador])['c'] ?? 0);
    }

    public function supervisorNotify(int $idSupervisor, string $tipo, string $titulo, ?string $mensaje = null, ?int $idReferencia = null): void
    {
        $this->db->insert('notificaciones_supervisor', [
            'id_supervisor' => $idSupervisor,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'id_referencia' => $idReferencia,
        ]);
    }

    public function supervisorNotifications(int $idSupervisor, int $limite = 10): array
    {
        return $this->db->fetchAll("
            SELECT * FROM notificaciones_supervisor
            WHERE id_supervisor = :id
            ORDER BY created_at DESC LIMIT " . (int) $limite,
            ['id' => $idSupervisor]
        );
    }

    public function supervisorUnreadCount(int $idSupervisor): int
    {
        return (int) ($this->db->fetchOne("
            SELECT COUNT(*) as c FROM notificaciones_supervisor
            WHERE id_supervisor = :id AND leida = 0
        ", ['id' => $idSupervisor])['c'] ?? 0);
    }

    public function clienteNotify(int $idCliente, string $tipo, string $titulo, ?string $mensaje = null, ?int $idReferencia = null): void
    {
        $this->db->insert('notificaciones_cliente', [
            'id_cliente' => $idCliente,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'id_referencia' => $idReferencia,
        ]);
    }

    public function clienteNotifications(int $idCliente, int $limite = 10): array
    {
        return $this->db->fetchAll("
            SELECT * FROM notificaciones_cliente
            WHERE id_cliente = :id
            ORDER BY created_at DESC LIMIT " . (int) $limite,
            ['id' => $idCliente]
        );
    }

    public function clienteUnreadCount(int $idCliente): int
    {
        return (int) ($this->db->fetchOne("
            SELECT COUNT(*) as c FROM notificaciones_cliente
            WHERE id_cliente = :id AND leida = 0
        ", ['id' => $idCliente])['c'] ?? 0);
    }

    public function markAsRead(string $type, int $userId): void
    {
        $table = match ($type) {
            'vendedor' => 'notificaciones_vendedor',
            'operador' => 'notificaciones_operador',
            'supervisor' => 'notificaciones_supervisor',
            'cliente' => 'notificaciones_cliente',
            default => null,
        };
        if ($table) {
            $column = $type === 'cliente' ? 'id_cliente' : "id_{$type}";
            $this->db->query("UPDATE {$table} SET leida = 1 WHERE {$column} = :id", ['id' => $userId]);
        }
    }
}
