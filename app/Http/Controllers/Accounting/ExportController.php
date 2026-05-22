<?php
namespace App\Http\Controllers\Accounting;

use App\Core\Controller;
use App\Core\Database;

class ExportController extends Controller
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function exportar(array $params): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 3, 6]);

        $tipo = $params['tipo'] ?? 'csv';
        $reporte = $params['reporte'] ?? '';
        $fecha = $this->getParam('fecha', date('Y-m-d'));
        $anio = $this->getParam('anio', date('Y'));
        $mes = $this->getParam('mes', date('m'));
        $idCuenta = (int) $this->getParam('cuenta', 0);
        $fecha_desde = $this->getParam('fecha_desde', date('Y-01-01'));
        $fecha_hasta = $this->getParam('fecha_hasta', date('Y-m-d'));

        $data = $this->getReportData($reporte, $fecha, $anio, $mes, $idCuenta, $fecha_desde, $fecha_hasta);
        if (!$data) {
            set_flash('error', 'Reporte no encontrado');
            $this->redirect('/contabilidad');
        }

        if ($tipo === 'csv') {
            $this->exportCsv($reporte, $data);
        } else {
            $this->renderPrintView($reporte, $data);
        }
    }

    private function getReportData(string $reporte, string $fecha, int $anio, int $mes, int $idCuenta, string $desde, string $hasta): ?array
    {
        return match ($reporte) {
            'balance' => $this->getBalanceData($fecha),
            'resultados' => $this->getResultadosData($anio),
            'balanza' => $this->getBalanzaData($fecha),
            'libro-diario' => $this->getLibroDiarioData($desde, $hasta),
            'libro-mayor' => $idCuenta ? $this->getLibroMayorData($idCuenta, $desde, $hasta) : null,
            'flujo-efectivo' => $this->getFlujoData($anio, $mes),
            default => null,
        };
    }

    private function getBalanceData(string $fecha): array
    {
        return $this->db->fetchAll("
            SELECT c.codigo, c.nombre, c.tipo, c.naturaleza,
                   COALESCE(SUM(CASE WHEN p.estatus = 'activo' THEN
                       CASE WHEN c.naturaleza = 'deudora' THEN pd.cargo - pd.abono
                       ELSE pd.abono - pd.cargo END
                   ELSE 0 END), 0) as saldo
            FROM plan_cuentas c
            LEFT JOIN polizas_detalle pd ON c.id_cuenta = pd.id_cuenta
            LEFT JOIN polizas p ON pd.id_poliza = p.id_poliza AND p.fecha <= :fecha
            WHERE c.tipo IN ('activo', 'pasivo', 'capital')
            GROUP BY c.id_cuenta ORDER BY c.codigo
        ", ['fecha' => $fecha]);
    }

    private function getResultadosData(int $anio): array
    {
        return $this->db->fetchAll("
            SELECT c.codigo, c.nombre, c.tipo, c.naturaleza,
                   COALESCE(SUM(CASE WHEN c.naturaleza = 'acreedora' THEN pd.abono - pd.cargo ELSE pd.cargo - pd.abono END), 0) as saldo
            FROM plan_cuentas c
            JOIN polizas_detalle pd ON c.id_cuenta = pd.id_cuenta
            JOIN polizas p ON pd.id_poliza = p.id_poliza AND p.estatus = 'activo'
            WHERE c.tipo IN ('ingreso', 'gasto') AND YEAR(p.fecha) = :anio
            GROUP BY c.id_cuenta HAVING saldo != 0 ORDER BY c.codigo
        ", ['anio' => $anio]);
    }

    private function getBalanzaData(string $fecha): array
    {
        return $this->db->fetchAll("
            SELECT c.codigo, c.nombre, c.tipo, c.naturaleza,
                   COALESCE(SUM(CASE WHEN p.estatus = 'activo' THEN pd.cargo ELSE 0 END), 0) as total_cargo,
                   COALESCE(SUM(CASE WHEN p.estatus = 'activo' THEN pd.abono ELSE 0 END), 0) as total_abono
            FROM plan_cuentas c
            LEFT JOIN polizas_detalle pd ON c.id_cuenta = pd.id_cuenta
            LEFT JOIN polizas p ON pd.id_poliza = p.id_poliza AND p.fecha <= :fecha
            GROUP BY c.id_cuenta ORDER BY c.codigo
        ", ['fecha' => $fecha]);
    }

    private function getLibroDiarioData(string $desde, string $hasta): array
    {
        return $this->db->fetchAll("
            SELECT p.folio, p.fecha, p.concepto as poliza_concepto, pc.codigo, pc.nombre as cuenta_nombre, pd.cargo, pd.abono
            FROM polizas_detalle pd
            JOIN polizas p ON pd.id_poliza = p.id_poliza AND p.estatus = 'activo'
            LEFT JOIN plan_cuentas pc ON pd.id_cuenta = pc.id_cuenta
            WHERE p.fecha BETWEEN :desde AND :hasta ORDER BY p.fecha, p.id_poliza
        ", ['desde' => $desde, 'hasta' => $hasta]);
    }

    private function getLibroMayorData(int $idCuenta, string $desde, string $hasta): array
    {
        return $this->db->fetchAll("
            SELECT p.fecha, p.folio, p.concepto, pd.cargo, pd.abono
            FROM polizas_detalle pd
            JOIN polizas p ON pd.id_poliza = p.id_poliza AND p.estatus = 'activo'
            WHERE pd.id_cuenta = :cuenta AND p.fecha BETWEEN :desde AND :hasta
            ORDER BY p.fecha, p.id_poliza
        ", ['cuenta' => $idCuenta, 'desde' => $desde, 'hasta' => $hasta]);
    }

    private function getFlujoData(int $anio, int $mes): array
    {
        $where = "YEAR(p.fecha) = :anio AND p.estatus = 'activo'";
        $params = ['anio' => $anio];
        if ($mes > 0) { $where .= " AND MONTH(p.fecha) = :mes"; $params['mes'] = $mes; }
        return $this->db->fetchAll("
            SELECT c.codigo, c.nombre, c.tipo,
                   COALESCE(SUM(pd.cargo), 0) as total_cargo, COALESCE(SUM(pd.abono), 0) as total_abono
            FROM plan_cuentas c
            JOIN polizas_detalle pd ON c.id_cuenta = pd.id_cuenta
            JOIN polizas p ON pd.id_poliza = p.id_poliza
            WHERE {$where} GROUP BY c.id_cuenta ORDER BY c.codigo
        ", $params);
    }

    private function exportCsv(string $reporte, array $data): void
    {
        $filename = "{$reporte}_" . date('Ymd') . ".csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        fclose($output);
        exit;
    }

    private function renderPrintView(string $reporte, array $data): void
    {
        $title = match ($reporte) {
            'balance' => 'Balance General',
            'resultados' => 'Estado de Resultados',
            'balanza' => 'Balanza de Comprobación',
            'libro-diario' => 'Libro Diario',
            'libro-mayor' => 'Libro Mayor',
            'flujo-efectivo' => 'Flujo de Efectivo',
            default => $reporte,
        };
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head><meta charset="UTF-8"><title><?= $title ?></title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: 'Courier New', monospace; padding: 20px; font-size: 10pt; }
            h1 { text-align: center; font-size: 14pt; margin-bottom: 5px; }
            h2 { text-align: center; font-size: 10pt; color: #666; margin-bottom: 20px; font-weight: normal; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th { background: #1a1a2e; color: #fff; padding: 6px 8px; font-size: 8pt; text-transform: uppercase; letter-spacing: 1px; text-align: left; }
            th.right { text-align: right; }
            td { padding: 4px 8px; border-bottom: 1px solid #ddd; }
            td.right { text-align: right; }
            .total { font-weight: bold; background: #f0f2f5; }
            .grand-total { font-weight: bold; font-size: 11pt; background: #1a1a2e; color: #fff; }
            .footer { text-align: center; font-size: 7pt; color: #aaa; margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px; }
            @media print { .no-print { display: none; } }
        </style>
        </head>
        <body>
            <div class="no-print" style="text-align:right;margin-bottom:10px;">
                <button onclick="window.print()">Imprimir / PDF</button>
            </div>
            <h1>PLASTI FRUS</h1>
            <h2><?= $title ?></h2>
            <table>
                <thead>
                    <tr>
                        <?php if (!empty($data)): ?>
                        <?php foreach (array_keys($data[0]) as $col): ?>
                        <th class="<?= in_array($col, ['saldo','cargo','abono','total_cargo','total_abono','presupuesto','real_mes']) ? 'right' : '' ?>"><?= str_replace('_', ' ', ucfirst($col)) ?></th>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                    <tr>
                        <?php foreach ($row as $col => $val): ?>
                        <td class="<?= in_array($col, ['saldo','cargo','abono','total_cargo','total_abono','presupuesto','real_mes']) ? 'right' : '' ?>">
                            <?= in_array($col, ['saldo','cargo','abono','total_cargo','total_abono','presupuesto','real_mes']) ? number_format((float) $val, 2) : htmlspecialchars((string) $val) ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="footer">Plasti Frus — Sistema de Gestión — <?= date('d/m/Y H:i') ?></div>
            <script>window.print();</script>
        </body>
        </html>
        <?php
        exit;
    }
}
