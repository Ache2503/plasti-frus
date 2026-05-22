<?php
namespace App\Models;

use App\Core\Model;

class TicketSoporte extends Model
{
    protected $table = 'tickets_soporte';
    protected $primaryKey = 'id_ticket';

    public function getByCliente(int $idCliente): array
    {
        return $this->fetchAll("
            SELECT t.*, u.nombre_usuario,
                   (SELECT COUNT(*) FROM ticket_respuestas WHERE id_ticket = t.id_ticket) as total_respuestas
            FROM {$this->table} t
            LEFT JOIN usuarios u ON t.id_usuario = u.id_usuario
            WHERE t.id_cliente = :id
            ORDER BY t.created_at DESC
        ", ['id' => $idCliente]);
    }

    public function getWithRespuestas(int $idTicket, int $idCliente)
    {
        $ticket = $this->fetchOne("
            SELECT t.*, u.nombre_usuario,
                   c.razon_social as cliente_razon
            FROM {$this->table} t
            LEFT JOIN usuarios u ON t.id_usuario = u.id_usuario
            LEFT JOIN clientes c ON t.id_cliente = c.id_cliente
            WHERE t.id_ticket = :id AND t.id_cliente = :cliente
        ", ['id' => $idTicket, 'cliente' => $idCliente]);

        if (!$ticket) return null;

        $respuestas = $this->fetchAll("
            SELECT r.*, u.nombre_usuario
            FROM ticket_respuestas r
            LEFT JOIN usuarios u ON r.id_usuario = u.id_usuario
            WHERE r.id_ticket = :id
            ORDER BY r.created_at ASC
        ", ['id' => $idTicket]);

        $ticket['respuestas'] = $respuestas;
        return $ticket;
    }

    public function create(array $data): int
    {
        return $this->db->insert($this->table, [
            'id_cliente' => $data['id_cliente'],
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'],
            'prioridad' => $data['prioridad'] ?? 'media',
        ]);
    }

    public function addRespuesta(int $idTicket, int $idCliente, string $mensaje, ?string $archivo = null): int
    {
        $ticket = $this->find($idTicket);
        if (!$ticket || $ticket['id_cliente'] !== $idCliente) {
            return 0;
        }

        $id = $this->db->insert('ticket_respuestas', [
            'id_ticket' => $idTicket,
            'es_cliente' => 1,
            'mensaje' => $mensaje,
            'archivo' => $archivo,
        ]);

        $this->db->update($this->table, [
            'estatus' => 'abierto',
            'id_usuario' => null,
        ], 'id_ticket = :id', ['id' => $idTicket]);

        return $id;
    }

    public function cerrar(int $idTicket, int $idCliente): bool
    {
        $ticket = $this->find($idTicket);
        if (!$ticket || $ticket['id_cliente'] !== $idCliente) {
            return false;
        }
        $this->db->update($this->table, [
            'estatus' => 'cerrado',
        ], 'id_ticket = :id', ['id' => $idTicket]);
        return true;
    }

    public function countAbiertosByCliente(int $idCliente): int
    {
        return (int) ($this->fetchOne("
            SELECT COUNT(*) as c FROM {$this->table}
            WHERE id_cliente = :id AND estatus IN ('abierto', 'respondido')
        ", ['id' => $idCliente])['c'] ?? 0);
    }
}
