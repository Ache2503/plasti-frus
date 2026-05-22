<?php
namespace App\Http\Controllers\Accounting;

use App\Core\Controller;
use App\Core\Database;

class FacturasController extends Controller
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function checkAccess(): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 3, 6]);
    }

    public function index(): void
    {
        $this->checkAccess();
        $data = [
            'facturas' => $this->getAllFacturas(),
            'pageTitle' => 'Facturas',
        ];
        $this->view('facturas/index', $data);
    }

    public function solicitudes(): void
    {
        $this->checkAccess();
        $data = [
            'solicitudes' => $this->getAllSolicitudes(),
            'pageTitle' => 'Solicitudes de Factura',
        ];
        $this->view('facturas/solicitudes', $data);
    }

    public function procesar(array $params): void
    {
        $this->checkAccess();
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/facturas/solicitudes');
        }
        $solicitud = $this->getSolicitudPendiente($params['id']);
        if (!$solicitud) {
            set_flash('error', 'Solicitud no encontrada o ya procesada');
            $this->redirect('/facturas/solicitudes');
        }

        $monto = $solicitud['cantidad_vendida'] * $solicitud['precio_unitario'];
        $subtotal = round($monto / 1.16, 2);
        $iva = round($monto - $subtotal, 2);

        $idFactura = $this->db->insert('facturas', [
            'id_venta' => $solicitud['id_venta'],
            'fecha_emision' => date('Y-m-d'),
            'fecha_vencimiento' => date('Y-m-d', strtotime('+30 days')),
            'monto_total' => $monto,
            'subtotal' => $subtotal,
            'iva' => $iva,
            'moneda' => $solicitud['moneda'] ?? 'MXN',
            'estatus' => 'emitida',
        ]);

        $this->db->update('solicitudes_factura', [
            'estatus' => 'procesada',
            'id_usuario_procesa' => $_SESSION['user_id'],
            'fecha_procesada' => date('Y-m-d H:i:s'),
            'id_factura' => $idFactura,
        ], 'id_solicitud = :id', ['id' => $params['id']]);

        registrar_log('procesar_factura', 'solicitud_factura', $params['id'], "Factura #{$idFactura} creada");
        set_flash('success', "Factura #{$idFactura} generada correctamente");
        $this->redirect('/facturas/solicitudes');
    }

    public function contabilizar(array $params): void
    {
        $this->checkAccess();
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/facturas');
        }

        $factura = $this->getFacturaConCliente($params['id']);
        if (!$factura) {
            set_flash('error', 'Factura no encontrada');
            $this->redirect('/facturas');
        }

        if ($factura['contabilizada']) {
            set_flash('error', 'Esta factura ya está contabilizada');
            $this->redirect('/facturas');
        }

        $fechaTs = strtotime($factura['fecha_emision']);
        $mes = (int) date('m', $fechaTs);
        $anio = (int) date('Y', $fechaTs);

        if ($this->periodoEstaCerrado($mes, $anio)) {
            set_flash('error', "El periodo {$mes}/{$anio} está cerrado. No se puede contabilizar.");
            $this->redirect('/facturas');
        }

        $folio = $this->generarFolioPoliza($fechaTs);
        $cliente = $factura['razon_social'] ?? 'Cliente #' . $factura['id_venta'];
        $concepto = "Factura #{$factura['id_factura']} - {$cliente}";

        $this->db->beginTransaction();
        try {
            $idPoliza = $this->db->insert('polizas', [
                'folio' => $folio,
                'tipo' => 'ingreso',
                'concepto' => $concepto,
                'fecha' => $factura['fecha_emision'],
                'created_by' => (int) $_SESSION['user_id'],
            ]);

            $this->db->insert('polizas_detalle', [
                'id_poliza' => $idPoliza,
                'id_cuenta' => 5,
                'concepto' => $concepto,
                'cargo' => $factura['monto_total'],
                'abono' => 0,
            ]);
            $this->db->insert('polizas_detalle', [
                'id_poliza' => $idPoliza,
                'id_cuenta' => 31,
                'concepto' => $concepto,
                'cargo' => 0,
                'abono' => $factura['subtotal'],
            ]);
            $this->db->insert('polizas_detalle', [
                'id_poliza' => $idPoliza,
                'id_cuenta' => 21,
                'concepto' => $concepto,
                'cargo' => 0,
                'abono' => $factura['iva'],
            ]);

            $this->db->update('facturas', [
                'contabilizada' => 1,
                'id_poliza' => $idPoliza,
            ], 'id_factura = :id', ['id' => $params['id']]);

            $this->db->commit();
            registrar_log('contabilizar_factura', 'factura', $params['id'], "Factura #{$params['id']} contabilizada → Póliza {$folio}");
            set_flash('success', "Factura contabilizada correctamente. Póliza {$folio} generada.");
        } catch (\Exception $e) {
            $this->db->rollback();
            set_flash('error', 'Error al contabilizar: ' . $e->getMessage());
        }
        $this->redirect('/facturas');
    }

    public function rechazar(array $params): void
    {
        $this->checkAccess();
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/facturas/solicitudes');
        }
        $this->db->update('solicitudes_factura', [
            'estatus' => 'rechazada',
            'id_usuario_procesa' => $_SESSION['user_id'],
            'fecha_procesada' => date('Y-m-d H:i:s'),
        ], 'id_solicitud = :id', ['id' => $params['id']]);

        registrar_log('rechazar_factura', 'solicitud_factura', $params['id'], 'Rechazada por admin');
        set_flash('success', 'Solicitud rechazada');
        $this->redirect('/facturas/solicitudes');
    }

    private function getAllFacturas(): array
    {
        return $this->db->fetchAll(
            "SELECT f.*, c.razon_social, v.cantidad_vendida, v.precio_unitario,
                    p.nombre as producto_nombre, po.folio as poliza_folio
             FROM facturas f
             LEFT JOIN ventas v ON f.id_venta = v.id_venta
             LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
             LEFT JOIN productos p ON v.id_producto = p.id_producto
             LEFT JOIN polizas po ON f.id_poliza = po.id_poliza
             ORDER BY f.fecha_emision DESC, f.id_factura DESC"
        );
    }

    private function getAllSolicitudes(): array
    {
        return $this->db->fetchAll(
            "SELECT s.*, c.razon_social, c.rfc, v.cantidad_vendida, v.precio_unitario, v.moneda,
                    p.nombre as producto_nombre, v.fecha_venta, u.nombre_usuario as procesado_por
             FROM solicitudes_factura s
             LEFT JOIN clientes c ON s.id_cliente = c.id_cliente
             LEFT JOIN ventas v ON s.id_venta = v.id_venta
             LEFT JOIN productos p ON v.id_producto = p.id_producto
             LEFT JOIN usuarios u ON s.id_usuario_procesa = u.id_usuario
             ORDER BY s.fecha_solicitud DESC"
        );
    }

    private function getSolicitudPendiente(string $id): ?array
    {
        $solicitud = $this->db->fetchOne(
            "SELECT s.*, v.cantidad_vendida, v.precio_unitario, v.moneda
             FROM solicitudes_factura s
             INNER JOIN ventas v ON s.id_venta = v.id_venta
             WHERE s.id_solicitud = :id AND s.estatus = 'pendiente'",
            ['id' => $id]
        );
        return $solicitud ?: null;
    }

    private function getFacturaConCliente(string $id): ?array
    {
        $factura = $this->db->fetchOne(
            "SELECT f.*, c.razon_social
             FROM facturas f
             LEFT JOIN ventas v ON f.id_venta = v.id_venta
             LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
             WHERE f.id_factura = :id",
            ['id' => $id]
        );
        return $factura ?: null;
    }

    private function periodoEstaCerrado(int $mes, int $anio): bool
    {
        $periodo = $this->db->fetchOne(
            "SELECT * FROM periodos_contables WHERE mes = :m AND anio = :y",
            ['m' => $mes, 'y' => $anio]
        );
        return $periodo && $periodo['cerrado'];
    }

    private function generarFolioPoliza(int $fechaTs): string
    {
        $folioBase = 'POL-' . date('Ym', $fechaTs) . '-';
        $ultimo = $this->db->fetchOne(
            "SELECT folio FROM polizas WHERE folio LIKE :prefix ORDER BY id_poliza DESC LIMIT 1",
            ['prefix' => "{$folioBase}%"]
        );
        $num = $ultimo ? ((int) substr($ultimo['folio'], -4)) + 1 : 1;
        return $folioBase . str_pad((string) $num, 4, '0', STR_PAD_LEFT);
    }
}
