<?php
namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class TicketSoporte extends Model
{
    protected $table = 'tickets_soporte';
    protected $primaryKey = 'id_ticket';

    public static function getUsuariosSoporte(): array
    {
        $db = Database::getInstance();
        return $db->fetchAll("
            SELECT id_usuario, nombre_usuario, id_rol
            FROM usuarios
            WHERE id_rol IN (1, 3)
            ORDER BY nombre_usuario
        ");
    }

    public function getByCliente(int $idCliente): array
    {
        $hasCreator = $this->db->columnExists($this->table, 'id_usuario_creador');
        $creatorSelect = $hasCreator ? 'uc.nombre_usuario as usuario_creador,' : '';
        $creatorJoin = $hasCreator ? 'LEFT JOIN usuarios uc ON t.id_usuario_creador = uc.id_usuario' : '';

        return $this->fetchAll("
            SELECT t.*, u.nombre_usuario,
                   c.razon_social as cliente_razon,
                   c.correo as cliente_correo,
                   c.telefono as cliente_telefono,
                   {$creatorSelect}
                   (SELECT COUNT(*) FROM ticket_respuestas WHERE id_ticket = t.id_ticket) as total_respuestas
            FROM {$this->table} t
            LEFT JOIN usuarios u ON t.id_usuario = u.id_usuario
            LEFT JOIN clientes c ON t.id_cliente = c.id_cliente
            {$creatorJoin}
            WHERE t.id_cliente = :id
            ORDER BY t.created_at DESC
        ", ['id' => $idCliente]);
    }

    public function getWithRespuestas(int $idTicket, int $idCliente)
    {
        $where = 't.id_ticket = :id';
        $params = ['id' => $idTicket];
        if ($idCliente > 0) {
            $where .= ' AND t.id_cliente = :cliente';
            $params['cliente'] = $idCliente;
        }
        $hasCreator = $this->db->columnExists($this->table, 'id_usuario_creador');
        $creatorSelect = $hasCreator ? ', uc.nombre_usuario as usuario_creador' : '';
        $creatorJoin = $hasCreator ? 'LEFT JOIN usuarios uc ON t.id_usuario_creador = uc.id_usuario' : '';

        $ticket = $this->fetchOne("
            SELECT t.*, u.nombre_usuario,
                   ua.nombre_usuario as usuario_asignado,
                   c.razon_social as cliente_razon,
                   c.rfc as cliente_rfc,
                   c.correo as cliente_correo,
                   c.telefono as cliente_telefono,
                   c.ciudad as cliente_ciudad,
                   c.estado as cliente_estado
                   {$creatorSelect}
            FROM {$this->table} t
            LEFT JOIN usuarios u ON t.id_usuario = u.id_usuario
            LEFT JOIN usuarios ua ON t.id_usuario_asignado = ua.id_usuario
            LEFT JOIN clientes c ON t.id_cliente = c.id_cliente
            {$creatorJoin}
            WHERE {$where}
        ", $params);

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
        $insert = [
            'id_cliente' => $data['id_cliente'],
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'],
            'prioridad' => $data['prioridad'] ?? 'media',
        ];
        if ($this->db->columnExists($this->table, 'id_usuario_creador')) {
            $insert['id_usuario_creador'] = $data['id_usuario_creador'] ?? null;
        }
        return $this->db->insert($this->table, $insert);
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
        if (!$ticket) return false;
        if ($idCliente > 0 && $ticket['id_cliente'] !== $idCliente) return false;
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

    public function getAll(?array $filters = []): array
    {
        $hasCreator = $this->db->columnExists($this->table, 'id_usuario_creador');
        $creatorSelect = $hasCreator ? 'uc.nombre_usuario as usuario_creador,' : '';
        $creatorJoin = $hasCreator ? 'LEFT JOIN usuarios uc ON t.id_usuario_creador = uc.id_usuario' : '';
        $where = '1=1';
        $params = [];

        if (!empty($filters['estatus'])) {
            $where .= ' AND t.estatus = :estatus';
            $params['estatus'] = $filters['estatus'];
        }
        if (!empty($filters['prioridad'])) {
            $where .= ' AND t.prioridad = :prioridad';
            $params['prioridad'] = $filters['prioridad'];
        }
        if (!empty($filters['search'])) {
            $where .= ' AND (t.titulo LIKE :search OR c.razon_social LIKE :search2)';
            $params['search'] = '%' . $filters['search'] . '%';
            $params['search2'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['id_usuario_asignado'])) {
            $where .= ' AND t.id_usuario_asignado = :asignado';
            $params['asignado'] = (int) $filters['id_usuario_asignado'];
        }

        return $this->fetchAll("
            SELECT t.*, c.razon_social as cliente_razon,
                   c.rfc as cliente_rfc,
                   c.correo as cliente_correo,
                   c.telefono as cliente_telefono,
                   u.nombre_usuario as usuario_atendio,
                   ua.nombre_usuario as usuario_asignado,
                   COALESCE(" . ($hasCreator ? "uc.nombre_usuario, " : "") . "cliente_user.nombre_usuario) as usuario_cliente,
                   {$creatorSelect}
                   (SELECT COUNT(*) FROM ticket_respuestas WHERE id_ticket = t.id_ticket) as total_respuestas
            FROM {$this->table} t
            LEFT JOIN clientes c ON t.id_cliente = c.id_cliente
            LEFT JOIN usuarios u ON t.id_usuario = u.id_usuario
            LEFT JOIN usuarios ua ON t.id_usuario_asignado = ua.id_usuario
            LEFT JOIN usuarios cliente_user ON cliente_user.id_cliente = t.id_cliente AND cliente_user.id_rol = 5
            {$creatorJoin}
            WHERE {$where}
            ORDER BY FIELD(t.estatus, 'abierto', 'respondido', 'cerrado'), t.created_at DESC
        ", $params);
    }

    public function getPendientesCount(): int
    {
        return (int) ($this->fetchOne("
            SELECT COUNT(*) as c FROM {$this->table}
            WHERE estatus IN ('abierto', 'respondido')
        ")['c'] ?? 0);
    }

    public function asignar(int $idTicket, int $idUsuario): bool
    {
        $this->db->update($this->table, [
            'id_usuario_asignado' => $idUsuario,
        ], 'id_ticket = :id', ['id' => $idTicket]);
        return true;
    }

    public function abrir(int $idTicket): bool
    {
        $this->db->update($this->table, [
            'estatus' => 'abierto',
        ], 'id_ticket = :id', ['id' => $idTicket]);
        return true;
    }

    public function responderComoStaff(int $idTicket, int $idUsuario, string $mensaje, ?string $archivo = null): int
    {
        $id = $this->db->insert('ticket_respuestas', [
            'id_ticket' => $idTicket,
            'id_usuario' => $idUsuario,
            'es_cliente' => 0,
            'mensaje' => $mensaje,
            'archivo' => $archivo,
        ]);

        $this->db->update($this->table, [
            'estatus' => 'respondido',
            'id_usuario' => $idUsuario,
        ], 'id_ticket = :id', ['id' => $idTicket]);

        return $id;
    }
}
