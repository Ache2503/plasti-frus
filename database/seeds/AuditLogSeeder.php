<?php
namespace Database\Seeds;

class AuditLogSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = $this->db->fetchAll("SELECT id_usuario, nombre_usuario FROM usuarios");
        if (empty($usuarios)) return;

        $acciones = [
            'login' => ['Sistema', 'Usuarios'],
            'logout' => ['Sistema', 'Usuarios'],
            'create' => ['Materiales', 'Productos', 'Ordenes', 'Clientes', 'Proveedores'],
            'update' => ['Materiales', 'Productos', 'Ordenes', 'Clientes', 'Proveedores'],
            'delete' => ['Materiales', 'Productos'],
            'view' => ['Reportes', 'Dashboard', 'Produccion'],
        ];

        $today = new \DateTime();

        $entries = [
            ['accion' => 'login', 'entidad' => 'Sistema', 'detalle' => 'Inicio de sesión exitoso'],
            ['accion' => 'login', 'entidad' => 'Sistema', 'detalle' => 'Inicio de sesión exitoso'],
            ['accion' => 'logout', 'entidad' => 'Sistema', 'detalle' => 'Cierre de sesión'],
            ['accion' => 'login', 'entidad' => 'Sistema', 'detalle' => 'Inicio de sesión exitoso'],
            ['accion' => 'create', 'entidad' => 'Productos', 'detalle' => 'Creación de nuevo producto'],
            ['accion' => 'update', 'entidad' => 'Productos', 'detalle' => 'Actualización de precio de producto'],
            ['accion' => 'create', 'entidad' => 'Ordenes', 'detalle' => 'Creación de orden de producción'],
            ['accion' => 'update', 'entidad' => 'Ordenes', 'detalle' => 'Actualización de estatus de orden'],
            ['accion' => 'create', 'entidad' => 'Materiales', 'detalle' => 'Registro de nuevo material'],
            ['accion' => 'update', 'entidad' => 'Materiales', 'detalle' => 'Actualización de stock de material'],
            ['accion' => 'create', 'entidad' => 'Clientes', 'detalle' => 'Registro de nuevo cliente'],
            ['accion' => 'update', 'entidad' => 'Clientes', 'detalle' => 'Actualización de datos de cliente'],
            ['accion' => 'view', 'entidad' => 'Reportes', 'detalle' => 'Descarga de reporte de producción'],
            ['accion' => 'view', 'entidad' => 'Dashboard', 'detalle' => 'Visualización de dashboard'],
            ['accion' => 'update', 'entidad' => 'Ordenes', 'detalle' => 'Asignación de operador a orden'],
            ['accion' => 'delete', 'entidad' => 'Productos', 'detalle' => 'Eliminación de producto obsoleto'],
            ['accion' => 'create', 'entidad' => 'Proveedores', 'detalle' => 'Registro de nuevo proveedor'],
            ['accion' => 'update', 'entidad' => 'Proveedores', 'detalle' => 'Actualización de datos de proveedor'],
            ['accion' => 'login', 'entidad' => 'Sistema', 'detalle' => 'Inicio de sesión exitoso'],
            ['accion' => 'logout', 'entidad' => 'Sistema', 'detalle' => 'Cierre de sesión'],
            ['accion' => 'create', 'entidad' => 'Ordenes', 'detalle' => 'Creación de orden de producción'],
            ['accion' => 'update', 'entidad' => 'Productos', 'detalle' => 'Actualización de existencias'],
        ];

        foreach ($entries as $i => $entry) {
            $usuario = $usuarios[array_rand($usuarios)];
            $diasAtras = rand(0, 6);
            $horas = rand(6, 20);
            $minutos = rand(0, 59);
            $fecha = (clone $today)->modify("-{$diasAtras} days");
            $fecha->setTime($horas, $minutos, rand(0, 59));
            $fechaStr = $fecha->format('Y-m-d H:i:s');

            $this->db->getConnection()->exec(
                "INSERT INTO audit_log (usuario_id, usuario_nombre, accion, entidad, detalle, ip, user_agent, created_at) VALUES (
                    {$usuario['id_usuario']},
                    '{$usuario['nombre_usuario']}',
                    '{$entry['accion']}',
                    '{$entry['entidad']}',
                    '{$entry['detalle']}',
                    '192.168.1." . rand(10, 254) . "',
                    'Mozilla/5.0',
                    '{$fechaStr}'
                )"
            );
        }
    }
}
