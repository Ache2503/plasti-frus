<?php
namespace App\Http\Controllers\System;

use App\Core\Controller;

class ReporteController extends Controller
{
    private function checkVendedor(): void
    {
        $this->requireAuth();
        if (!es_vendedor()) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }
    }

    public function vendedor(): void
    {
        $this->checkVendedor();
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

    public function exportar(): void
    {
        $this->checkVendedor();
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

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="reporte_' . $tipo . '.csv"');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        match ($tipo) {
            'productos' => (function() use ($output, $datos) {
                fputcsv($output, ['Producto', 'Cantidad', 'Total']);
                foreach ($datos as $d) fputcsv($output, [$d['nombre'] ?? 'N/A', $d['cantidad'] ?? 0, $d['total'] ?? 0]);
            })(),
            'clientes' => (function() use ($output, $datos) {
                fputcsv($output, ['Cliente', 'Ventas', 'Total']);
                foreach ($datos as $d) fputcsv($output, [$d['razon_social'] ?? 'N/A', $d['total_ventas'] ?? 0, $d['total'] ?? 0]);
            })(),
            'pipeline' => (function() use ($output, $datos) {
                fputcsv($output, ['Etapa', 'Oportunidades', 'Valor Total']);
                foreach ($datos as $d) fputcsv($output, [$d['etapa'], $d['total'] ?? 0, $d['valor_total'] ?? 0]);
            })(),
            'comisiones' => (function() use ($output, $datos) {
                fputcsv($output, ['Venta', 'Cliente', 'Comision', 'Estatus', 'Fecha']);
                foreach ($datos as $d) fputcsv($output, [$d['venta_folio'] ?? '#'.$d['id_venta'], $d['cliente'] ?? 'N/A', $d['monto_comision'], $d['estatus'], $d['fecha_calculo'] ?? '']);
            })(),
        };

        fclose($output);
        exit;
    }
}
