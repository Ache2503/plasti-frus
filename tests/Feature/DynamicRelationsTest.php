<?php
namespace Tests\Feature;

use App\Http\Controllers\Production\OrdenesController;
use Tests\TestCase;

class DynamicRelationsTest extends TestCase
{
    public function test_can_create_and_edit_maintenance_with_dynamic_technician(): void
    {
        $tecnico = $this->createInternalUser(3, 'tec');
        $maquina = $this->createMachine();
        $tipo = $this->catalogId('tipos_mantenimiento', 'preventivo');

        $id = $this->db()->insert('mantenimientos_maquinas', [
            'id_maquina' => $maquina,
            'fecha_mantenimiento' => date('Y-m-d'),
            'tipo_mantenimiento' => 'preventivo',
            'id_tipo_mantenimiento' => $tipo,
            'tecnico_responsable' => $tecnico['nombre_completo'],
            'id_tecnico_responsable' => $tecnico['id_usuario'],
            'horas_paro' => 1,
            'resultado' => 'pendiente',
        ]);

        $nuevoTecnico = $this->createInternalUser(3, 'tec_edit');
        $this->db()->update('mantenimientos_maquinas', [
            'id_tecnico_responsable' => $nuevoTecnico['id_usuario'],
            'tecnico_responsable' => $nuevoTecnico['nombre_completo'],
            'resultado' => 'completado',
        ], 'id_mantenimiento = :id', ['id' => $id]);

        $row = $this->db()->fetchOne("
            SELECT m.*, COALESCE(NULLIF(TRIM(CONCAT(e.nombre, ' ', e.apellido_paterno)), ''), u.nombre_usuario, m.tecnico_responsable) as tecnico_nombre
            FROM mantenimientos_maquinas m
            LEFT JOIN usuarios u ON m.id_tecnico_responsable = u.id_usuario
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            WHERE m.id_mantenimiento = :id
        ", ['id' => $id]);

        $this->assertSame($nuevoTecnico['id_usuario'], (int) $row['id_tecnico_responsable']);
        $this->assertSame($nuevoTecnico['nombre_completo'], $row['tecnico_nombre']);
    }

    public function test_can_create_and_edit_downtime_with_dynamic_operator(): void
    {
        $operador = $this->createInternalUser(2, 'op');
        $maquina = $this->createMachine();
        $motivo = $this->catalogId('motivos_paro', 'falla_mecanica');

        $id = $this->db()->insert('bitacora_paros', [
            'id_maquina' => $maquina,
            'fecha' => date('Y-m-d'),
            'hora_inicio' => '08:00',
            'hora_fin' => null,
            'duracion_paro' => 0,
            'motivo_paro' => 'Falla mecanica',
            'id_motivo_paro' => $motivo,
            'operador' => $operador['nombre_completo'],
            'id_operador' => $operador['id_usuario'],
            'estatus' => 'activo',
        ]);

        $nuevoOperador = $this->createInternalUser(2, 'op_edit');
        $this->db()->update('bitacora_paros', [
            'id_operador' => $nuevoOperador['id_usuario'],
            'operador' => $nuevoOperador['nombre_completo'],
            'hora_fin' => '09:00',
            'duracion_paro' => 1,
            'estatus' => 'resuelto',
        ], 'id_bitacora = :id', ['id' => $id]);

        $row = $this->db()->fetchOne("
            SELECT bp.*, COALESCE(NULLIF(TRIM(CONCAT(e.nombre, ' ', e.apellido_paterno)), ''), u.nombre_usuario, bp.operador) as operador_nombre
            FROM bitacora_paros bp
            LEFT JOIN usuarios u ON bp.id_operador = u.id_usuario
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            WHERE bp.id_bitacora = :id
        ", ['id' => $id]);

        $this->assertSame($nuevoOperador['id_usuario'], (int) $row['id_operador']);
        $this->assertSame($nuevoOperador['nombre_completo'], $row['operador_nombre']);
        $this->assertSame('resuelto', $row['estatus']);
    }

    public function test_can_register_kardex_movement_with_dynamic_operator(): void
    {
        $operador = $this->createInternalUser(2, 'kardex_op');
        $material = $this->createMaterial();

        $id = $this->db()->insert('kardex_materiales', [
            'id_material' => $material,
            'fecha' => date('Y-m-d'),
            'movimiento' => 'entrada',
            'cantidad' => 10,
            'stock_final' => 10,
            'operador' => $operador['nombre_completo'],
            'id_operador' => $operador['id_usuario'],
        ]);

        $row = $this->db()->fetchOne("
            SELECT k.*, m.nombre as material_nombre,
                   COALESCE(NULLIF(TRIM(CONCAT(e.nombre, ' ', e.apellido_paterno)), ''), u.nombre_usuario, k.operador) as operador_nombre
            FROM kardex_materiales k
            LEFT JOIN materiales m ON k.id_material = m.id_material
            LEFT JOIN usuarios u ON k.id_operador = u.id_usuario
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            WHERE k.id_kardex = :id
        ", ['id' => $id]);

        $this->assertSame($operador['nombre_completo'], $row['operador_nombre']);
        $this->assertNotEmpty($row['material_nombre']);
    }

    public function test_can_register_quality_inspection_with_dynamic_inspector(): void
    {
        $inspector = $this->createInternalUser(3, 'insp');
        $producto = $this->createProduct();

        $id = 'INS-' . strtoupper(substr(uniqid(), -5));
        $this->db()->insert('inspecciones_calidad', [
            'id_inspeccion' => $id,
            'id_producto' => $producto,
            'fecha_inspeccion' => date('Y-m-d'),
            'muestreo_piezas' => 10,
            'piezas_aprobadas' => 9,
            'piezas_rechazadas' => 1,
            'inspector' => $inspector['nombre_completo'],
            'id_inspector' => $inspector['id_usuario'],
            'resultado' => 'rechazado',
        ]);

        $row = $this->db()->fetchOne("
            SELECT i.*, p.nombre as producto_nombre,
                   COALESCE(NULLIF(TRIM(CONCAT(e.nombre, ' ', e.apellido_paterno)), ''), u.nombre_usuario, i.inspector) as inspector_nombre
            FROM inspecciones_calidad i
            LEFT JOIN productos p ON i.id_producto = p.id_producto
            LEFT JOIN usuarios u ON i.id_inspector = u.id_usuario
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            WHERE i.id_inspeccion = :id
        ", ['id' => $id]);

        $this->assertSame($inspector['nombre_completo'], $row['inspector_nombre']);
        $this->assertNotEmpty($row['producto_nombre']);
    }

    public function test_can_create_and_edit_sale_with_dynamic_client_and_product(): void
    {
        $cliente = $this->createClient();
        $producto = $this->createProduct();
        $nuevoProducto = $this->createProduct('Producto Venta Edit');

        $id = $this->db()->insert('ventas', [
            'id_cliente' => $cliente,
            'id_producto' => $producto,
            'fecha_venta' => date('Y-m-d'),
            'cantidad_vendida' => 2,
            'precio_unitario' => 50,
            'estatus' => 'pendiente',
        ]);
        $this->db()->update('ventas', [
            'id_producto' => $nuevoProducto,
            'cantidad_vendida' => 3,
        ], 'id_venta = :id', ['id' => $id]);

        $row = $this->db()->fetchOne("
            SELECT v.*, c.razon_social as cliente_nombre, p.nombre as producto_nombre
            FROM ventas v
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            LEFT JOIN productos p ON v.id_producto = p.id_producto
            WHERE v.id_venta = :id
        ", ['id' => $id]);

        $this->assertSame($cliente, (int) $row['id_cliente']);
        $this->assertSame($nuevoProducto, (int) $row['id_producto']);
        $this->assertNotEmpty($row['cliente_nombre']);
        $this->assertSame('Producto Venta Edit', $row['producto_nombre']);
    }

    public function test_can_create_and_edit_production_order_with_dynamic_relations(): void
    {
        $producto = $this->createProduct();
        $maquina = $this->createMachine();
        $molde = $this->createMold();
        $receta = $this->createRecipe($producto, $maquina);

        $id = $this->db()->insert('ordenes_cabecera', [
            'id_producto' => $producto,
            'id_maquina' => $maquina,
            'id_molde' => $molde,
            'id_receta' => $receta,
            'cantidad_planificada' => 100,
            'fecha' => date('Y-m-d'),
            'turno' => 'matutino',
            'estatus' => 'pendiente',
        ]);

        $nuevaMaquina = $this->createMachine('Maquina Edit');
        $this->db()->update('ordenes_cabecera', [
            'id_maquina' => $nuevaMaquina,
            'cantidad_planificada' => 125,
        ], 'id_orden_cabe = :id', ['id' => $id]);

        $row = $this->db()->fetchOne("
            SELECT oc.*, p.nombre as producto_nombre, m.nombre as maquina_nombre,
                   md.nombre_molde as molde_nombre, rc.version as receta_version
            FROM ordenes_cabecera oc
            LEFT JOIN productos p ON oc.id_producto = p.id_producto
            LEFT JOIN maquinas m ON oc.id_maquina = m.id_maquina
            LEFT JOIN moldes md ON oc.id_molde = md.id_molde
            LEFT JOIN recetas_cabecera rc ON oc.id_receta = rc.id_receta_cabe
            WHERE oc.id_orden_cabe = :id
        ", ['id' => $id]);

        $this->assertSame($nuevaMaquina, (int) $row['id_maquina']);
        $this->assertSame('Maquina Edit', $row['maquina_nombre']);
        $this->assertNotEmpty($row['producto_nombre']);
        $this->assertNotEmpty($row['molde_nombre']);
        $this->assertSame('1.0', $row['receta_version']);
    }

    public function test_can_create_client_address_for_existing_client(): void
    {
        $cliente = $this->createClient();
        $id = $this->db()->insert('direcciones_cliente', [
            'id_cliente' => $cliente,
            'alias' => 'Planta',
            'calle' => 'Av. Prueba',
            'ciudad' => 'Guadalajara',
            'estado' => 'Jalisco',
            'codigo_postal' => '44100',
            'predeterminada' => 1,
        ]);

        $row = $this->db()->fetchOne("
            SELECT d.*, c.razon_social
            FROM direcciones_cliente d
            INNER JOIN clientes c ON d.id_cliente = c.id_cliente
            WHERE d.id_direccion = :id
        ", ['id' => $id]);

        $this->assertSame($cliente, (int) $row['id_cliente']);
        $this->assertNotEmpty($row['razon_social']);
    }

    public function test_invalid_dynamic_ids_are_rejected_by_application_lookup(): void
    {
        $controller = new OrdenesController();
        $method = new \ReflectionMethod($controller, 'validateOrdenRelations');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($controller, 999999, null, null, null));
        $this->assertNull($this->db()->fetchOne('SELECT id_usuario FROM usuarios WHERE id_usuario = :id', ['id' => 999999]) ?: null);
    }

    public function test_dynamic_filters_return_expected_records(): void
    {
        $producto = $this->createProduct('Producto Filtro');
        $this->db()->insert('ordenes_cabecera', [
            'id_producto' => $producto,
            'cantidad_planificada' => 77,
            'fecha' => date('Y-m-d'),
            'turno' => 'vespertino',
            'estatus' => 'pendiente',
        ]);

        $rows = $this->db()->fetchAll("
            SELECT oc.*, p.nombre as producto_nombre
            FROM ordenes_cabecera oc
            LEFT JOIN productos p ON oc.id_producto = p.id_producto
            WHERE oc.id_producto = :producto AND oc.turno = :turno
        ", ['producto' => $producto, 'turno' => 'vespertino']);

        $this->assertCount(1, $rows);
        $this->assertSame('Producto Filtro', $rows[0]['producto_nombre']);
    }

    private function catalogId(string $table, string $slug): int
    {
        $row = $this->db()->fetchOne("SELECT * FROM {$table} WHERE slug = :slug", ['slug' => $slug]);
        if ($row) {
            $primary = 'id_' . substr($table, 0, -1);
            return (int) ($row[$primary] ?? reset($row));
        }

        $primary = match ($table) {
            'tipos_mantenimiento' => 'id_tipo_mantenimiento',
            'motivos_paro' => 'id_motivo_paro',
            'motivos_rechazo' => 'id_motivo_rechazo',
            default => 'id',
        };
        return $this->db()->insert($table, [
            'nombre' => ucfirst(str_replace('_', ' ', $slug)),
            'slug' => $slug,
            'activo' => 1,
        ]);
    }

    private function createInternalUser(int $rol, string $prefix): array
    {
        $emp = $this->db()->insert('empleados', [
            'nombre' => ucfirst($prefix),
            'apellido_paterno' => 'Prueba',
            'correo' => $prefix . uniqid() . '@test.local',
            'estatus' => 'activo',
        ]);
        $id = $this->db()->insert('usuarios', [
            'id_empleado' => $emp,
            'nombre_usuario' => $prefix . '_' . uniqid(),
            'password_hash' => password_hash('test123456', PASSWORD_DEFAULT),
            'id_rol' => $rol,
            'activo' => 1,
        ]);
        return [
            'id_usuario' => $id,
            'nombre_completo' => ucfirst($prefix) . ' Prueba',
        ];
    }

    private function createClient(): int
    {
        return $this->db()->insert('clientes', [
            'razon_social' => 'Cliente Test ' . uniqid(),
            'rfc' => 'XAXX010101000',
            'ciudad' => 'CDMX',
            'estado' => 'CDMX',
            'activo' => 1,
        ]);
    }

    private function createProduct(string $nombre = 'Producto Test'): int
    {
        return $this->db()->insert('productos', [
            'codigo' => 'P-' . uniqid(),
            'nombre' => $nombre,
            'familia' => 'Prueba',
            'precio_venta' => 10,
            'stock_actual' => 0,
            'peso_unitario_grs' => 20,
        ]);
    }

    private function createMachine(string $nombre = 'Maquina Test'): int
    {
        return $this->db()->insert('maquinas', [
            'nombre' => $nombre,
            'modelo' => 'M-1',
            'estatus' => 'activo',
        ]);
    }

    private function createMold(): int
    {
        return $this->db()->insert('moldes', [
            'nombre_molde' => 'Molde Test ' . uniqid(),
            'numero_cavidades' => 2,
            'estatus' => 'activo',
        ]);
    }

    private function createRecipe(int $producto, int $maquina): int
    {
        return $this->db()->insert('recetas_cabecera', [
            'id_producto' => $producto,
            'id_maquina' => $maquina,
            'version' => '1.0',
            'fecha_version' => date('Y-m-d'),
        ]);
    }

    private function createMaterial(): int
    {
        return $this->db()->insert('materiales', [
            'tipo' => 'Polimero',
            'nombre' => 'Material Test ' . uniqid(),
            'presentacion' => 'Saco',
            'unidad_medida' => 'kg',
            'stock_actual_kg' => 0,
            'punto_reorden_kg' => 0,
        ]);
    }
}
