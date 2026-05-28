<?php
function asset(string $path): string
{
    return APP_URL . '/assets/' . ltrim($path, '/');
}

function url(string $path = ''): string
{
    return APP_URL . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function old(string $key, $default = '')
{
    return $_SESSION['_old'][$key] ?? $default;
}

function csrf_token(): string
{
    if (!isset($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function verify_csrf(string $token): bool
{
    return isset($_SESSION['_csrf_token']) && hash_equals($_SESSION['_csrf_token'], $token);
}

function format_money($amount, string $currency = null): string
{
    $currency = $currency ?: APP_CURRENCY;
    return '$' . number_format((float) $amount, 2, '.', ',') . ' ' . $currency;
}

function format_date(?string $date, string $format = 'd/m/Y'): string
{
    if (!$date) return '';
    $dt = new DateTime($date);
    return $dt->format($format);
}

function format_datetime(?string $datetime, string $format = 'd/m/Y H:i'): string
{
    if (!$datetime) return '';
    $dt = new DateTime($datetime);
    return $dt->format($format);
}

function time_ago(?string $datetime): string
{
    if (!$datetime) return '';
    $now = new DateTime();
    $dt = new DateTime($datetime);
    $diff = $now->getTimestamp() - $dt->getTimestamp();
    if ($diff < 60) return 'hace unos segundos';
    if ($diff < 3600) return 'hace ' . floor($diff / 60) . ' minutos';
    if ($diff < 86400) return 'hace ' . floor($diff / 3600) . ' horas';
    return 'hace ' . floor($diff / 86400) . ' días';
}

function truncate(string $text, int $limit = 100): string
{
    if (mb_strlen($text) <= $limit) return $text;
    return mb_substr($text, 0, $limit) . '...';
}

function flash_message(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function redirect_back(): void
{
    $referer = $_SERVER['HTTP_REFERER'] ?? '/';
    $refererHost = parse_url($referer, PHP_URL_HOST);
    $currentHost = $_SERVER['HTTP_HOST'] ?? null;
    if ($refererHost !== null && $currentHost !== null && strcasecmp($refererHost, $currentHost) !== 0) {
        $referer = '/';
    }
    header("Location: {$referer}");
    exit;
}

function is_active(string $path): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $base = dirname($_SERVER['SCRIPT_NAME']);
    if ($base !== '/') {
        $uri = str_replace($base, '', $uri);
    }
    $uri = parse_url($uri, PHP_URL_PATH);
    return strpos($uri, $path) === 0 ? 'active' : '';
}

function safe_string(?string $str): string
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function generate_folio(string $prefix = 'ORD'): string
{
    return $prefix . '-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

function user_rol(): ?int
{
    return $_SESSION['user_rol'] ?? null;
}

function user_rol_nombre(): string
{
    return $_SESSION['rol_nombre'] ?? '';
}

function es_admin(): bool
{
    return user_rol() === 1;
}

function es_supervisor(): bool
{
    return user_rol() === 3;
}

function contar_oportunidades_abiertas(): int
{
    if (!isset($_SESSION['user_id'])) return 0;
    $db = \App\Core\Database::getInstance();
    $r = $db->fetchOne("SELECT COUNT(*) as t FROM oportunidades WHERE id_vendedor = :id AND activo = 1 AND etapa NOT IN ('cerrado_ganado','cerrado_perdido')", ['id' => (int) $_SESSION['user_id']]);
    return (int) ($r['t'] ?? 0);
}

function es_operador(): bool
{
    return user_rol() === 2;
}

function es_vendedor(): bool
{
    return user_rol() === ROL_VENDEDOR;
}

function es_cliente(): bool
{
    return user_rol() === 5;
}

function user_nombre_completo(): ?string
{
    return $_SESSION['empleado_nombre'] ?? $_SESSION['user_name'] ?? null;
}

function es_contador(): bool
{
    return user_rol() === 6;
}

function es_contador_o_admin(): bool
{
    return in_array(user_rol(), [1, 6]);
}

function user_id_cliente(): ?int
{
    return $_SESSION['user_id_cliente'] ?? null;
}

function notificar_vendedor(int $idVendedor, string $tipo, string $titulo, ?string $mensaje = null, ?int $idReferencia = null): void
{
    $db = \App\Core\Database::getInstance();
    $db->insert('notificaciones_vendedor', [
        'id_vendedor' => $idVendedor,
        'tipo' => $tipo,
        'titulo' => $titulo,
        'mensaje' => $mensaje,
        'id_referencia' => $idReferencia,
    ]);
}

function vendedor_notificaciones(int $idVendedor, int $limite = 10): array
{
    $db = \App\Core\Database::getInstance();
    return $db->query("
        SELECT * FROM notificaciones_vendedor
        WHERE id_vendedor = :id
        ORDER BY created_at DESC
        LIMIT " . (int) $limite,
        ['id' => $idVendedor]
    )->fetchAll();
}

function vendedor_notificaciones_no_leidas(int $idVendedor): int
{
    $db = \App\Core\Database::getInstance();
    return (int) $db->fetchOne("
        SELECT COUNT(*) as c FROM notificaciones_vendedor
        WHERE id_vendedor = :id AND leida = 0
    ", ['id' => $idVendedor])['c'];
}

function notificar_operador(int $idOperador, string $tipo, string $titulo, ?string $mensaje = null, ?int $idReferencia = null): void
{
    $db = \App\Core\Database::getInstance();
    $db->insert('notificaciones_operador', [
        'id_operador' => $idOperador,
        'tipo' => $tipo,
        'titulo' => $titulo,
        'mensaje' => $mensaje,
        'id_referencia' => $idReferencia,
    ]);
}

function operador_notificaciones(int $idOperador, int $limite = 10): array
{
    $db = \App\Core\Database::getInstance();
    return $db->fetchAll("
        SELECT * FROM notificaciones_operador
        WHERE id_operador = :id
        ORDER BY created_at DESC
        LIMIT " . (int) $limite,
        ['id' => $idOperador]
    );
}

function operador_notificaciones_no_leidas(int $idOperador): int
{
    $db = \App\Core\Database::getInstance();
    return (int) ($db->fetchOne("
        SELECT COUNT(*) as c FROM notificaciones_operador
        WHERE id_operador = :id AND leida = 0
    ", ['id' => $idOperador])['c'] ?? 0);
}

function verificar_acceso_operador(): array
{
    $db = \App\Core\Database::getInstance();
    $userId = (int) ($_SESSION['user_id'] ?? 0);
    $empleado = $db->fetchOne("
        SELECT e.id_empleado FROM usuarios u
        JOIN empleados e ON u.id_empleado = e.id_empleado
        WHERE u.id_usuario = :id AND u.id_rol = 2
    ", ['id' => $userId]);

    if (!$empleado) {
        return ['permitido' => true];
    }

    $horario = $db->fetchOne("
        SELECT * FROM horarios_operador
        WHERE id_empleado = :id AND activo = 1
        LIMIT 1
    ", ['id' => $empleado['id_empleado']]);

    if (!$horario) {
        return [
            'permitido' => false,
            'mensaje' => 'No tienes un horario asignado. Contacta a tu supervisor.',
        ];
    }

    $horaActual = date('H:i:s');
    $horaInicio = $horario['hora_inicio'];
    $horaFin = $horario['hora_fin'];

    $dentroHorario = false;
    if ($horaInicio < $horaFin) {
        $dentroHorario = $horaActual >= $horaInicio && $horaActual <= $horaFin;
    } else {
        $dentroHorario = $horaActual >= $horaInicio || $horaActual <= $horaFin;
    }

    if ($dentroHorario) {
        return ['permitido' => true];
    }

    $override = $db->fetchOne("
        SELECT * FROM accesos_extraordinarios
        WHERE id_empleado = :id AND expiracion > NOW()
        ORDER BY expiracion DESC LIMIT 1
    ", ['id' => $empleado['id_empleado']]);

    if ($override) {
        return [
            'permitido' => true,
            'override' => true,
            'motivo' => $override['motivo'],
        ];
    }

    return [
        'permitido' => false,
        'mensaje' => 'Fuera de tu horario laboral (' . date('H:i', strtotime($horaInicio)) . ' - ' . date('H:i', strtotime($horaFin)) . '). Contacta a tu supervisor para autorización.',
    ];
}

function registrar_log(string $accion, string $entidad, $id_entidad = null, ?string $detalle = null): void
{
    $db = \App\Core\Database::getInstance();
    $db->insert('log_actividad', [
        'id_usuario' => $_SESSION['user_id'] ?? null,
        'accion' => $accion,
        'entidad' => $entidad,
        'id_entidad' => $id_entidad,
        'detalle' => $detalle,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
    ]);
}

function requireRolMultiple(array $roles): void
{
    if (!in_array(user_rol(), $roles)) {
        set_flash('error', 'No tienes permisos para acceder a esta sección');
        redirect('/');
    }
}

function puedeEliminar(): bool
{
    return in_array(user_rol(), [1, 3]);
}

function contabilidad_permiso(string $accion): bool
{
    $rol = user_rol();
    return match ($accion) {
        'ver'           => in_array($rol, [1, 3, 6]),
        'crear'         => in_array($rol, [1, 6]),
        'editar'        => in_array($rol, [1, 6]),
        'eliminar'      => in_array($rol, [1]),
        'cancelar'      => in_array($rol, [1, 6]),
        'cerrar_periodo'=> in_array($rol, [1]),
        default         => false,
    };
}

function notificar_supervisor(int $idSupervisor, string $tipo, string $titulo, ?string $mensaje = null, ?int $idReferencia = null): void
{
    $db = \App\Core\Database::getInstance();
    $db->insert('notificaciones_supervisor', [
        'id_supervisor' => $idSupervisor,
        'tipo' => $tipo,
        'titulo' => $titulo,
        'mensaje' => $mensaje,
        'id_referencia' => $idReferencia,
    ]);
}

function supervisor_notificaciones(int $idSupervisor, int $limite = 10): array
{
    $db = \App\Core\Database::getInstance();
    return $db->fetchAll("
        SELECT * FROM notificaciones_supervisor
        WHERE id_supervisor = :id
        ORDER BY created_at DESC
        LIMIT " . (int) $limite,
        ['id' => $idSupervisor]
    );
}

function supervisor_notificaciones_no_leidas(int $idSupervisor): int
{
    $db = \App\Core\Database::getInstance();
    return (int) ($db->fetchOne("
        SELECT COUNT(*) as c FROM notificaciones_supervisor
        WHERE id_supervisor = :id AND leida = 0
    ", ['id' => $idSupervisor])['c'] ?? 0);
}

function puede_acceder(string $modulo): bool
{
    $accesos = [
        1 => ['dashboard', 'materiales', 'productos', 'recetas', 'ordenes', 'maquinas', 'moldes', 'clientes', 'proveedores', 'ventas', 'kpi', 'reportes', 'usuarios', 'config', 'calidad', 'kardex', 'incidencias', 'mantenimiento', 'notificaciones', 'contabilidad'],
        2 => ['dashboard', 'productos', 'recetas', 'ordenes', 'maquinas', 'moldes', 'materiales'],
         3 => ['dashboard', 'materiales', 'productos', 'recetas', 'ordenes', 'maquinas', 'moldes', 'clientes', 'proveedores', 'ventas', 'kpi', 'reportes', 'calidad', 'kardex', 'incidencias', 'mantenimiento', 'notificaciones', 'facturas', 'usuarios', 'contabilidad'],
         4 => ['dashboard', 'productos', 'clientes', 'ventas'],
        5 => ['dashboard', 'catalogo'],
        6 => ['dashboard', 'contabilidad', 'facturas', 'clientes', 'proveedores', 'ventas', 'notificaciones', 'reportes'],
    ];
    $rol = user_rol();
    return isset($accesos[$rol]) && in_array($modulo, $accesos[$rol]);
}

function paginate(\App\Core\Database $db, string $baseQuery, array $params = [], int $perPage = 20, string $countField = '*'): \App\Core\Pagination
{
    $page = max(1, (int)($_GET['page'] ?? 1));
    $countQuery = preg_replace('/SELECT .*? FROM/i', "SELECT COUNT({$countField}) as total FROM", $baseQuery, 1);
    $total = (int)($db->fetchOne($countQuery, $params)['total'] ?? 0);
    $offset = ($page - 1) * $perPage;
    $items = $db->fetchAll($baseQuery . " LIMIT {$perPage} OFFSET {$offset}", $params);
    return new \App\Core\Pagination($items, $total, $page, $perPage);
}
