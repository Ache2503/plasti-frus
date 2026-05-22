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

    public function exportExcel(string $tipo = ''): void
    {
        $this->checkAccess();
        $tipo = $tipo ?: $this->getParam('tipo', 'productos');
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

    public function exportPDF(string $tipo = ''): void
    {
        $this->checkAccess();
        $tipo = $tipo ?: $this->getParam('tipo', 'productos');
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

        $html = '<html><head><meta charset="utf-8"><style>table{width:100%;border-collapse:collapse}th,td{border:1px solid #ddd;padding:6px;text-align:left}th{background:#333;color:#fff}</style></head><body>';
        $html .= '<h2>Reporte: ' . htmlspecialchars(ucfirst($tipo)) . '</h2><p>Periodo: ' . htmlspecialchars($desde) . ' a ' . htmlspecialchars($hasta) . '</p>';
        $html .= '<table><thead><tr>';
        match ($tipo) {
            'productos' => $html .= '<th>Producto</th><th>Cantidad</th><th>Total</th>',
            'clientes' => $html .= '<th>Cliente</th><th>Ventas</th><th>Total</th>',
            'pipeline' => $html .= '<th>Etapa</th><th>Oportunidades</th><th>Valor Total</th>',
            'comisiones' => $html .= '<th>Venta</th><th>Cliente</th><th>Comision</th><th>Estatus</th>',
        };
        $html .= '</tr></thead><tbody>';
        foreach ($datos as $d) {
            $html .= '<tr>';
            match ($tipo) {
                'productos' => $html .= '<td>' . htmlspecialchars($d['nombre'] ?? 'N/A') . '</td><td>' . ((int) ($d['cantidad'] ?? 0)) . '</td><td>' . number_format($d['total'] ?? 0, 2) . '</td>',
                'clientes' => $html .= '<td>' . htmlspecialchars($d['razon_social'] ?? 'N/A') . '</td><td>' . ((int) ($d['total_ventas'] ?? 0)) . '</td><td>' . number_format($d['total'] ?? 0, 2) . '</td>',
                'pipeline' => $html .= '<td>' . htmlspecialchars($d['etapa']) . '</td><td>' . ((int) ($d['total'] ?? 0)) . '</td><td>' . number_format($d['valor_total'] ?? 0, 2) . '</td>',
                'comisiones' => $html .= '<td>' . htmlspecialchars($d['venta_folio'] ?? '#' . $d['id_venta']) . '</td><td>' . htmlspecialchars($d['cliente'] ?? 'N/A') . '</td><td>' . number_format($d['monto_comision'] ?? 0, 2) . '</td><td>' . htmlspecialchars($d['estatus'] ?? '') . '</td>',
            };
            $html .= '</tr>';
        }
        $html .= '</tbody></table></body></html>';

        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: inline; filename="reporte_' . $tipo . '.html"');
        echo $html;
        exit;
    }
}
