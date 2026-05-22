<?php
namespace App\Http\Controllers\System;

use App\Core\Controller;

class ReportesVendedorController extends Controller
{
    private function checkAccess(): void
    {
        $this->requireAuth();
        if (!es_vendedor()) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }
    }

    public function index(): void
    {
        $this->checkAccess();
        $userId = (int) $_SESSION['user_id'];
        $tipo = $this->getParam('tipo', 'productos');
        $desde = $this->getParam('desde', date('Y-m-d', strtotime('-1 year')));
        $hasta = $this->getParam('hasta', date('Y-m-d'));

        $model = new \App\Models\Vendedor();
        $oportunidadModel = new \App\Models\Oportunidad();

        $datos = match ($tipo) {
            'productos' => $model->getVentasByProducto($userId, $desde, $hasta),
            'clientes' => $model->getVentasByCliente($userId, $desde, $hasta),
            'pipeline' => $oportunidadModel->getPipelineResumen($userId),
            'comisiones' => $model->getComisiones($userId),
            default => [],
        };

        $etapasLabels = [
            'prospeccion' => 'Prospección', 'contactado' => 'Contactado',
            'propuesta' => 'Propuesta enviada', 'negociacion' => 'Negociación',
            'cerrado_ganado' => 'Cerrado ganado', 'cerrado_perdido' => 'Cerrado perdido',
        ];

        $this->view('vendedor/reportes', [
            'datos' => $datos,
            'tipo_reporte' => $tipo,
            'desde' => $desde,
            'hasta' => $hasta,
            'etapasLabels' => $etapasLabels,
            'pageTitle' => 'Reportes Comerciales',
        ]);
    }

    public function generar(): void
    {
        $this->checkAccess();
        $tipo = $this->postParam('tipo', 'productos');
        $desde = $this->postParam('desde', date('Y-m-d', strtotime('-1 year')));
        $hasta = $this->postParam('hasta', date('Y-m-d'));
        $this->redirect('/vendedor/reportes?tipo=' . urlencode($tipo) . '&desde=' . urlencode($desde) . '&hasta=' . urlencode($hasta));
    }

    private function buildExportData(string $tipo): array
    {
        $desde = $this->getParam('desde', date('Y-m-d', strtotime('-1 year')));
        $hasta = $this->getParam('hasta', date('Y-m-d'));
        $userId = (int) $_SESSION['user_id'];

        $model = new \App\Models\Vendedor();
        $oportunidadModel = new \App\Models\Oportunidad();

        $datos = match ($tipo) {
            'productos' => $model->getVentasByProducto($userId, $desde, $hasta),
            'clientes' => $model->getVentasByCliente($userId, $desde, $hasta),
            'pipeline' => $oportunidadModel->getPipelineResumen($userId),
            'comisiones' => $model->getComisiones($userId),
            default => [],
        };

        [$headers, $rows] = match ($tipo) {
            'productos' => [
                ['Producto', 'Cantidad', 'Total'],
                array_map(fn($d) => [$d['nombre'] ?? 'N/A', (int)($d['cantidad'] ?? 0), number_format($d['total'] ?? 0, 2)], $datos),
            ],
            'clientes' => [
                ['Cliente', 'Ventas', 'Total'],
                array_map(fn($d) => [$d['razon_social'] ?? 'N/A', (int)($d['total_ventas'] ?? 0), number_format($d['total'] ?? 0, 2)], $datos),
            ],
            'pipeline' => [
                ['Etapa', 'Oportunidades', 'Valor Total'],
                array_map(fn($d) => [$d['etapa'], (int)($d['total'] ?? 0), number_format($d['valor_total'] ?? 0, 2)], $datos),
            ],
            'comisiones' => [
                ['Venta', 'Cliente', 'Comisión', 'Estatus'],
                array_map(fn($d) => [$d['venta_folio'] ?? '#' . $d['id_venta'], $d['cliente'] ?? 'N/A', number_format($d['monto_comision'] ?? 0, 2), $d['estatus'] ?? ''], $datos),
            ],
        };

        $title = match ($tipo) {
            'productos' => 'Reporte de Productos',
            'clientes' => 'Reporte de Clientes',
            'pipeline' => 'Pipeline de Ventas',
            'comisiones' => 'Reporte de Comisiones',
            default => 'Reporte',
        };

        return [$title, $headers, $rows];
    }

    public function exportExcel(string $tipo = ''): void
    {
        $this->checkAccess();
        $tipo = $tipo ?: $this->getParam('tipo', 'productos');
        [$title, $headers, $rows] = $this->buildExportData($tipo);
        $service = new \App\Services\ExportService($title, $headers, $rows);
        $service->excel();
    }

    public function exportPDF(string $tipo = ''): void
    {
        $this->checkAccess();
        $tipo = $tipo ?: $this->getParam('tipo', 'productos');
        [$title, $headers, $rows] = $this->buildExportData($tipo);
        $service = new \App\Services\ExportService($title, $headers, $rows);
        $service->pdf();
    }
}
