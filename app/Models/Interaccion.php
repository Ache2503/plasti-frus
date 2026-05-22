<?php
namespace App\Models;

use App\Core\Model;

class Interaccion extends Model
{
    protected $table = 'interacciones';
    protected $primaryKey = 'id_interaccion';

    public const TIPOS = ['llamada', 'correo', 'reunion', 'nota'];

    public function findByCliente(int $clienteId, int $vendedorId): array
    {
        return $this->fetchAll("
            SELECT i.*, CONCAT(e.nombre, ' ', e.apellido_paterno) as vendedor_nombre
            FROM interacciones i
            LEFT JOIN usuarios u ON i.id_vendedor = u.id_usuario
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            WHERE i.id_cliente = :cliente AND i.id_vendedor = :vendedor
            ORDER BY i.fecha DESC
        ", ['cliente' => $clienteId, 'vendedor' => $vendedorId]);
    }

    public function getUltimaByCliente(int $clienteId, int $vendedorId): ?array
    {
        $r = $this->fetchOne("
            SELECT * FROM interacciones
            WHERE id_cliente = :cliente AND id_vendedor = :vendedor
            ORDER BY fecha DESC LIMIT 1
        ", ['cliente' => $clienteId, 'vendedor' => $vendedorId]);
        return $r ?: null;
    }

    public function getClientesSinSeguimiento(int $vendedorId, int $dias = 7): array
    {
        return $this->fetchAll("
            SELECT c.id_cliente, c.razon_social, MAX(i.fecha) as ultima_interaccion
            FROM clientes c
            LEFT JOIN interacciones i ON c.id_cliente = i.id_cliente AND i.id_vendedor = :vendedor
            WHERE c.id_vendedor = :vendedor2 AND c.activo = 1
            GROUP BY c.id_cliente
            HAVING ultima_interaccion IS NULL OR ultima_interaccion < DATE_SUB(NOW(), INTERVAL :dias DAY)
            ORDER BY ultima_interaccion ASC
        ", ['vendedor' => $vendedorId, 'vendedor2' => $vendedorId, 'dias' => $dias]);
    }
}
