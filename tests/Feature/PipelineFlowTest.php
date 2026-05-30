<?php
namespace Tests\Feature;

use App\Http\Controllers\Crm\OportunidadController;
use App\Models\Oportunidad;
use Tests\TestCase;

class PipelineFlowTest extends TestCase
{
    public function test_pipeline_lists_only_vendor_opportunities_for_vendor_role(): void
    {
        $vendedorA = $this->createVendor();
        $vendedorB = $this->createVendor();
        $clienteA = $this->createClient($vendedorA);
        $clienteB = $this->createClient($vendedorB);

        $this->createOpportunity($vendedorA, $clienteA, 'Oportunidad A', 'prospeccion', 1000);
        $this->createOpportunity($vendedorB, $clienteB, 'Oportunidad B', 'propuesta', 2000);

        $model = new Oportunidad();
        $rows = $model->findVisibleByUser($vendedorA, ROL_VENDEDOR);

        $this->assertCount(1, $rows);
        $this->assertSame('Oportunidad A', $rows[0]['titulo']);
        $this->assertSame('prospeccion', $rows[0]['etapa']);
    }

    public function test_admin_can_filter_pipeline_by_vendor(): void
    {
        $admin = $this->createAdmin();
        $vendedor = $this->createVendor();
        $otroVendedor = $this->createVendor();
        $this->createOpportunity($vendedor, $this->createClient($vendedor), 'Filtrada', 'negociacion', 5000);
        $this->createOpportunity($otroVendedor, $this->createClient($otroVendedor), 'No visible en filtro', 'contactado', 1000);

        $model = new Oportunidad();
        $rows = $model->findVisibleByUser($admin, 1, $vendedor);
        $total = $model->getTotalPipelineVisible($admin, 1, $vendedor);

        $this->assertCount(1, $rows);
        $this->assertSame('Filtrada', $rows[0]['titulo']);
        $this->assertSame(5000.0, $total);
    }

    public function test_pipeline_search_filters_by_stage_date_and_value(): void
    {
        $vendedor = $this->createVendor();
        $cliente = $this->createClient($vendedor);
        $this->createOpportunity($vendedor, $cliente, 'Grande', 'propuesta', 10000, date('Y-m-d', strtotime('+10 days')));
        $this->createOpportunity($vendedor, $cliente, 'Chica', 'prospeccion', 100, date('Y-m-d', strtotime('+1 day')));

        $rows = (new Oportunidad())->searchVisible([
            'etapa' => 'propuesta',
            'valor_min' => 1000,
            'fecha_desde' => date('Y-m-d'),
            'fecha_hasta' => date('Y-m-d', strtotime('+30 days')),
        ], $vendedor, ROL_VENDEDOR);

        $this->assertCount(1, $rows);
        $this->assertSame('Grande', $rows[0]['titulo']);
    }

    public function test_pipeline_summary_and_conversion_are_calculated_from_database(): void
    {
        $vendedor = $this->createVendor();
        $cliente = $this->createClient($vendedor);
        $this->createOpportunity($vendedor, $cliente, 'Ganada', 'cerrado_ganado', 3000);
        $this->createOpportunity($vendedor, $cliente, 'Perdida', 'cerrado_perdido', 9000);
        $this->createOpportunity($vendedor, $cliente, 'Abierta', 'negociacion', 2000);

        $model = new Oportunidad();
        $summary = $model->getPipelineResumenVisible($vendedor, ROL_VENDEDOR);
        $conversion = $model->getTasaConversionVisible($vendedor, ROL_VENDEDOR);
        $total = $model->getTotalPipelineVisible($vendedor, ROL_VENDEDOR);

        $this->assertSame(3, array_sum(array_map(fn($row) => (int) $row['total'], $summary)));
        $this->assertSame(33.3, $conversion['tasa']);
        $this->assertSame(5000.0, $total);
    }

    public function test_soft_deleted_opportunities_are_hidden(): void
    {
        $vendedor = $this->createVendor();
        $id = $this->createOpportunity($vendedor, $this->createClient($vendedor), 'Borrada', 'prospeccion', 100);
        $this->db()->update('oportunidades', ['activo' => 0], 'id_oportunidad = :id', ['id' => $id]);

        $rows = (new Oportunidad())->findVisibleByUser($vendedor, ROL_VENDEDOR);

        $this->assertCount(0, $rows);
    }

    public function test_client_must_belong_to_selected_vendor_when_assigned(): void
    {
        $vendedor = $this->createVendor();
        $otroVendedor = $this->createVendor();
        $cliente = $this->createClient($otroVendedor);

        $_SESSION['user_id'] = $vendedor;
        $_SESSION['user_rol'] = ROL_VENDEDOR;

        $controller = new OportunidadController();
        $method = new \ReflectionMethod($controller, 'clienteValido');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($controller, $cliente, $vendedor));
    }

    private function createOpportunity(int $vendedor, ?int $cliente, string $titulo, string $etapa, float $valor, ?string $fecha = null): int
    {
        return $this->db()->insert('oportunidades', [
            'id_vendedor' => $vendedor,
            'id_cliente' => $cliente,
            'titulo' => $titulo,
            'valor' => $valor,
            'etapa' => $etapa,
            'probabilidad' => Oportunidad::PROBABILIDADES[$etapa] ?? 0,
            'fecha_cierre_estimada' => $fecha,
            'activo' => 1,
        ]);
    }

    private function createVendor(): int
    {
        return $this->createUser(ROL_VENDEDOR, 'vend');
    }

    private function createAdmin(): int
    {
        return $this->createUser(1, 'admin');
    }

    private function createUser(int $rol, string $prefix): int
    {
        $empleado = $this->db()->insert('empleados', [
            'nombre' => ucfirst($prefix),
            'apellido_paterno' => uniqid(),
            'correo' => uniqid() . '@test.local',
            'estatus' => 'activo',
        ]);
        return $this->db()->insert('usuarios', [
            'id_empleado' => $empleado,
            'nombre_usuario' => $prefix . '_' . uniqid(),
            'password_hash' => password_hash('test123456', PASSWORD_DEFAULT),
            'id_rol' => $rol,
            'activo' => 1,
        ]);
    }

    private function createClient(?int $idVendedor = null): int
    {
        return $this->db()->insert('clientes', [
            'razon_social' => 'Cliente Pipeline ' . uniqid(),
            'rfc' => 'XAXX010101000',
            'id_vendedor' => $idVendedor,
            'activo' => 1,
        ]);
    }
}
