<?php
namespace App\Repositories;

use App\Core\Database;

class InteraccionRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByCliente(int $clienteId, int $vendedorId): array
    {
        return $this->db->fetchAll("
            SELECT i.*, CONCAT(e.nombre, ' ', e.apellido_paterno) as vendedor_nombre
            FROM interacciones i
            LEFT JOIN usuarios u ON i.id_vendedor = u.id_usuario
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            WHERE i.id_cliente = :cliente AND i.id_vendedor = :vendedor
            ORDER BY i.fecha DESC
        ", ['cliente' => $clienteId, 'vendedor' => $vendedorId]);
    }

    public function create(int $clienteId, int $vendedorId, string $tipo, string $descripcion, ?string $fecha = null): int
    {
        return $this->db->insert('interacciones', [
            'id_cliente' => $clienteId,
            'id_vendedor' => $vendedorId,
            'tipo' => $tipo,
            'descripcion' => $descripcion,
            'fecha' => $fecha ?? date('Y-m-d H:i:s'),
        ]);
    }

    public function getUltimaByCliente(int $clienteId, int $vendedorId): ?array
    {
        $r = $this->db->fetchOne("
            SELECT * FROM interacciones
            WHERE id_cliente = :cliente AND id_vendedor = :vendedor
            ORDER BY fecha DESC LIMIT 1
        ", ['cliente' => $clienteId, 'vendedor' => $vendedorId]);
        return $r ?: null;
    }
}
