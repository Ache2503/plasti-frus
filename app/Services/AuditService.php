<?php
namespace App\Services;

use App\Core\Database;

class AuditService
{
    private static ?Database $db = null;

    private static function db(): Database
    {
        if (!self::$db) self::$db = Database::getInstance();
        return self::$db;
    }

    public static function log(string $accion, string $entidad, string|int|null $entidadId = null, ?string $detalle = null): void
    {
        $usuarioId = $_SESSION['user_id'] ?? null;
        $usuarioNombre = $_SESSION['user_name'] ?? $_SESSION['empleado_nombre'] ?? 'Sistema';

        self::db()->insert('audit_log', [
            'usuario_id' => $usuarioId,
            'usuario_nombre' => $usuarioNombre,
            'accion' => $accion,
            'entidad' => $entidad,
            'entidad_id' => (string) $entidadId,
            'detalle' => $detalle,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function getAll(int $page = 1, int $perPage = 50, array $filters = []): array
    {
        $db = self::db();
        $where = [];
        $params = [];

        if (!empty($filters['accion'])) {
            $where[] = 'a.accion = :accion';
            $params['accion'] = $filters['accion'];
        }
        if (!empty($filters['entidad'])) {
            $where[] = 'a.entidad = :entidad';
            $params['entidad'] = $filters['entidad'];
        }
        if (!empty($filters['usuario'])) {
            $where[] = 'a.usuario_nombre LIKE :usuario';
            $params['usuario'] = '%' . $filters['usuario'] . '%';
        }
        if (!empty($filters['fecha_desde'])) {
            $where[] = 'a.created_at >= :fecha_desde';
            $params['fecha_desde'] = $filters['fecha_desde'] . ' 00:00:00';
        }
        if (!empty($filters['fecha_hasta'])) {
            $where[] = 'a.created_at <= :fecha_hasta';
            $params['fecha_hasta'] = $filters['fecha_hasta'] . ' 23:59:59';
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $total = (int)($db->fetchOne("SELECT COUNT(*) as total FROM audit_log a {$whereClause}", $params)['total'] ?? 0);
        $offset = ($page - 1) * $perPage;
        $items = $db->fetchAll(
            "SELECT a.*,
            CASE
                WHEN a.accion = 'INSERT' THEN 'Creaci&oacute;n'
                WHEN a.accion = 'UPDATE' THEN 'Actualizaci&oacute;n'
                WHEN a.accion = 'DELETE' THEN 'Eliminaci&oacute;n'
                WHEN a.accion = 'LOGIN' THEN 'Inicio de sesi&oacute;n'
                WHEN a.accion = 'LOGOUT' THEN 'Cierre de sesi&oacute;n'
                WHEN a.accion = 'EXPORT' THEN 'Exportaci&oacute;n'
                ELSE a.accion
            END as accion_label
            FROM audit_log a {$whereClause} ORDER BY a.created_at DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );
        return ['items' => $items, 'total' => $total, 'page' => $page, 'perPage' => $perPage];
    }

    public static function getActions(): array
    {
        return self::db()->fetchAll("SELECT DISTINCT accion FROM audit_log ORDER BY accion");
    }

    public static function getEntities(): array
    {
        return self::db()->fetchAll("SELECT DISTINCT entidad FROM audit_log ORDER BY entidad");
    }
}
