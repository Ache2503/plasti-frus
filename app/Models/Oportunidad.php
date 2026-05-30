<?php
namespace App\Models;

use App\Core\Model;

class Oportunidad extends Model
{
    protected $table = 'oportunidades';
    protected $primaryKey = 'id_oportunidad';

    public const ETAPAS = [
        'prospeccion' => 'Prospección',
        'contactado' => 'Contactado',
        'propuesta' => 'Propuesta enviada',
        'negociacion' => 'Negociación',
        'cerrado_ganado' => 'Cerrado ganado',
        'cerrado_perdido' => 'Cerrado perdido',
    ];

    public const PROBABILIDADES = [
        'prospeccion' => 10,
        'contactado' => 25,
        'propuesta' => 50,
        'negociacion' => 75,
        'cerrado_ganado' => 100,
        'cerrado_perdido' => 0,
    ];

    public function findByVendedor(int $vendedorId, ?string $etapa = null): array
    {
        $where = 'WHERE o.id_vendedor = :vendedor AND o.activo = 1';
        $params = ['vendedor' => $vendedorId];
        if ($etapa) {
            $where .= ' AND o.etapa = :etapa';
            $params['etapa'] = $etapa;
        }
        return $this->fetchAll("
            SELECT o.*, c.razon_social as cliente_nombre
            FROM {$this->table} o
            LEFT JOIN clientes c ON o.id_cliente = c.id_cliente
            {$where}
            ORDER BY o.created_at DESC
        ", $params);
    }

    public function findVisibleByUser(int $userId, int $rol, ?int $vendedorId = null): array
    {
        $where = 'WHERE o.activo = 1';
        $params = [];
        if ($rol === ROL_VENDEDOR) {
            $where .= ' AND o.id_vendedor = :vendedor';
            $params['vendedor'] = $userId;
        } elseif ($vendedorId) {
            $where .= ' AND o.id_vendedor = :vendedor';
            $params['vendedor'] = $vendedorId;
        }

        return $this->fetchAll("
            SELECT o.*, c.razon_social as cliente_nombre,
                   COALESCE(NULLIF(TRIM(CONCAT(e.nombre, ' ', e.apellido_paterno)), ''), u.nombre_usuario) as vendedor_nombre
            FROM {$this->table} o
            LEFT JOIN clientes c ON o.id_cliente = c.id_cliente
            LEFT JOIN usuarios u ON o.id_vendedor = u.id_usuario
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            {$where}
            ORDER BY o.created_at DESC
        ", $params);
    }

    public function getPipelineResumen(int $vendedorId): array
    {
        return $this->fetchAll("
            SELECT etapa, COUNT(*) as total, COALESCE(SUM(valor), 0) as valor_total
            FROM {$this->table}
            WHERE id_vendedor = :vendedor AND activo = 1
            GROUP BY etapa
            ORDER BY FIELD(etapa, 'prospeccion','contactado','propuesta','negociacion','cerrado_ganado','cerrado_perdido')
        ", ['vendedor' => $vendedorId]);
    }

    public function getPipelineResumenVisible(int $userId, int $rol, ?int $vendedorId = null): array
    {
        $where = 'WHERE activo = 1';
        $params = [];
        if ($rol === ROL_VENDEDOR) {
            $where .= ' AND id_vendedor = :vendedor';
            $params['vendedor'] = $userId;
        } elseif ($vendedorId) {
            $where .= ' AND id_vendedor = :vendedor';
            $params['vendedor'] = $vendedorId;
        }

        return $this->fetchAll("
            SELECT etapa, COUNT(*) as total, COALESCE(SUM(valor), 0) as valor_total
            FROM {$this->table}
            {$where}
            GROUP BY etapa
            ORDER BY FIELD(etapa, 'prospeccion','contactado','propuesta','negociacion','cerrado_ganado','cerrado_perdido')
        ", $params);
    }

    public function getTotalPipeline(int $vendedorId): float
    {
        $r = $this->fetchOne("
            SELECT COALESCE(SUM(valor), 0) as total
            FROM {$this->table}
            WHERE id_vendedor = :vendedor AND activo = 1 AND etapa NOT IN ('cerrado_perdido')
        ", ['vendedor' => $vendedorId]);
        return (float) ($r['total'] ?? 0);
    }

    public function getTotalPipelineVisible(int $userId, int $rol, ?int $vendedorId = null): float
    {
        $where = "WHERE activo = 1 AND etapa NOT IN ('cerrado_perdido')";
        $params = [];
        if ($rol === ROL_VENDEDOR) {
            $where .= ' AND id_vendedor = :vendedor';
            $params['vendedor'] = $userId;
        } elseif ($vendedorId) {
            $where .= ' AND id_vendedor = :vendedor';
            $params['vendedor'] = $vendedorId;
        }
        $r = $this->fetchOne("
            SELECT COALESCE(SUM(valor), 0) as total
            FROM {$this->table}
            {$where}
        ", $params);
        return (float) ($r['total'] ?? 0);
    }

    public function search(array $filters, int $vendedorId): array
    {
        $where = 'WHERE o.id_vendedor = :vendedor AND o.activo = 1';
        $params = ['vendedor' => $vendedorId];
        if (!empty($filters['etapa'])) {
            $where .= ' AND o.etapa = :etapa';
            $params['etapa'] = $filters['etapa'];
        }
        if (!empty($filters['valor_min'])) {
            $where .= ' AND o.valor >= :valor_min';
            $params['valor_min'] = (float) $filters['valor_min'];
        }
        if (!empty($filters['fecha_desde'])) {
            $where .= ' AND o.fecha_cierre_estimada >= :fecha_desde';
            $params['fecha_desde'] = $filters['fecha_desde'];
        }
        if (!empty($filters['fecha_hasta'])) {
            $where .= ' AND o.fecha_cierre_estimada <= :fecha_hasta';
            $params['fecha_hasta'] = $filters['fecha_hasta'];
        }
        return $this->fetchAll("
            SELECT o.*, c.razon_social as cliente_nombre
            FROM {$this->table} o
            LEFT JOIN clientes c ON o.id_cliente = c.id_cliente
            {$where}
            ORDER BY o.updated_at DESC
        ", $params);
    }

    public function searchVisible(array $filters, int $userId, int $rol, ?int $vendedorId = null): array
    {
        $where = 'WHERE o.activo = 1';
        $params = [];
        if ($rol === ROL_VENDEDOR) {
            $where .= ' AND o.id_vendedor = :vendedor';
            $params['vendedor'] = $userId;
        } elseif ($vendedorId) {
            $where .= ' AND o.id_vendedor = :vendedor';
            $params['vendedor'] = $vendedorId;
        }
        if (!empty($filters['etapa'])) {
            $where .= ' AND o.etapa = :etapa';
            $params['etapa'] = $filters['etapa'];
        }
        if (!empty($filters['valor_min'])) {
            $where .= ' AND o.valor >= :valor_min';
            $params['valor_min'] = (float) $filters['valor_min'];
        }
        if (!empty($filters['fecha_desde'])) {
            $where .= ' AND o.fecha_cierre_estimada >= :fecha_desde';
            $params['fecha_desde'] = $filters['fecha_desde'];
        }
        if (!empty($filters['fecha_hasta'])) {
            $where .= ' AND o.fecha_cierre_estimada <= :fecha_hasta';
            $params['fecha_hasta'] = $filters['fecha_hasta'];
        }

        return $this->fetchAll("
            SELECT o.*, c.razon_social as cliente_nombre,
                   COALESCE(NULLIF(TRIM(CONCAT(e.nombre, ' ', e.apellido_paterno)), ''), u.nombre_usuario) as vendedor_nombre
            FROM {$this->table} o
            LEFT JOIN clientes c ON o.id_cliente = c.id_cliente
            LEFT JOIN usuarios u ON o.id_vendedor = u.id_usuario
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            {$where}
            ORDER BY o.updated_at DESC
        ", $params);
    }

    public function getTasaConversion(int $vendedorId): array
    {
        $total = $this->fetchOne("
            SELECT COUNT(*) as total
            FROM {$this->table}
            WHERE id_vendedor = :vendedor AND activo = 1
        ", ['vendedor' => $vendedorId]);
        $ganados = $this->fetchOne("
            SELECT COUNT(*) as total
            FROM {$this->table}
            WHERE id_vendedor = :vendedor AND etapa = 'cerrado_ganado' AND activo = 1
        ", ['vendedor' => $vendedorId]);
        $t = (int) ($total['total'] ?? 0);
        $g = (int) ($ganados['total'] ?? 0);
        return [
            'total' => $t,
            'ganados' => $g,
            'tasa' => $t > 0 ? round($g / $t * 100, 1) : 0,
        ];
    }

    public function getTasaConversionVisible(int $userId, int $rol, ?int $vendedorId = null): array
    {
        $where = 'WHERE activo = 1';
        $params = [];
        if ($rol === ROL_VENDEDOR) {
            $where .= ' AND id_vendedor = :vendedor';
            $params['vendedor'] = $userId;
        } elseif ($vendedorId) {
            $where .= ' AND id_vendedor = :vendedor';
            $params['vendedor'] = $vendedorId;
        }
        $total = $this->fetchOne("SELECT COUNT(*) as total FROM {$this->table} {$where}", $params);
        $ganados = $this->fetchOne("SELECT COUNT(*) as total FROM {$this->table} {$where} AND etapa = 'cerrado_ganado'", $params);
        $t = (int) ($total['total'] ?? 0);
        $g = (int) ($ganados['total'] ?? 0);
        return [
            'total' => $t,
            'ganados' => $g,
            'tasa' => $t > 0 ? round($g / $t * 100, 1) : 0,
        ];
    }
}
