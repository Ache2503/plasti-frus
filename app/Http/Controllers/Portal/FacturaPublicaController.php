<?php
namespace App\Http\Controllers\Portal;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Ticket;

class FacturaPublicaController extends Controller
{
    private Database $db;
    private Ticket $ticket;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ticket = new Ticket();
    }

    public function buscarForm(): void
    {
        $data = [
            'pageTitle' => 'Facturación - Plasti Frus',
        ];
        $this->view('factura_publica/buscar', $data);
    }

    public function buscar(): void
    {
        $folio = trim($this->postParam('folio_unico', ''));
        if (empty($folio)) {
            set_flash('error', 'Ingresa el código de facturación');
            $this->redirect('/factura');
        }
        $ticket = $this->ticket->getByFolio($folio);
        if (!$ticket) {
            set_flash('error', 'Código inválido. Verifica tu ticket.');
            $this->redirect('/factura');
        }
        if ($ticket['venta_estatus'] !== 'completado') {
            set_flash('error', 'La venta asociada no está completada');
            $this->redirect('/factura');
        }
        $this->redirect('/factura/solicitar/' . $folio);
    }

    public function solicitarForm(array $params): void
    {
        $folio = $params['folio'];
        $ticket = $this->ticket->getByFolio($folio);
        if (!$ticket) {
            set_flash('error', 'Ticket no encontrado');
            $this->redirect('/factura');
        }
        $data = [
            'ticket' => $ticket,
            'pageTitle' => 'Solicitar Factura',
        ];
        $this->view('factura_publica/formulario', $data);
    }

    public function solicitar(array $params): void
    {
        $folio = $params['folio'];
        $ticket = $this->ticket->getByFolio($folio);
        if (!$ticket) {
            set_flash('error', 'Ticket no encontrado');
            $this->redirect('/factura');
        }

        $existe = $this->db->fetchOne(
            "SELECT id_solicitud FROM solicitudes_factura WHERE id_venta = :id",
            ['id' => $ticket['id_venta']]
        );
        if ($existe) {
            set_flash('error', 'Ya existe una solicitud de factura para esta venta');
            $this->redirect('/factura');
        }

        $idCliente = $ticket['id_cliente'];
        $razon = trim($this->postParam('razon_social', ''));
        $rfc = strtoupper(trim($this->postParam('rfc', '')));
        $cp = trim($this->postParam('codigo_postal', ''));
        $regimen = trim($this->postParam('regimen_fiscal', ''));
        $uso = trim($this->postParam('uso_cfdi', ''));
        $correo = trim($this->postParam('correo_fiscal', ''));

        $errors = [];
        if (empty($razon)) $errors[] = 'La razón social es obligatoria';
        if (empty($rfc) || !validate_rfc($rfc)) $errors[] = 'RFC inválido';
        if (empty($cp)) $errors[] = 'El código postal es obligatorio';
        if (empty($regimen)) $errors[] = 'Selecciona un régimen fiscal';
        if (empty($uso)) $errors[] = 'Selecciona un uso de CFDI';
        if (!empty($correo) && !validate_email($correo)) $errors[] = 'Correo fiscal inválido';

        if (!empty($errors)) {
            $_SESSION['_old'] = $_POST;
            set_flash('error', implode('<br>', $errors));
            $this->redirect('/factura/solicitar/' . $folio);
        }

        $this->db->update('clientes', [
            'razon_social' => $razon,
            'rfc' => $rfc,
            'codigo_postal' => $cp,
            'regimen_fiscal' => $regimen,
            'uso_cfdi' => $uso,
            'correo_fiscal' => $correo ?: $ticket['correo_fiscal'],
        ], 'id_cliente = :id', ['id' => $idCliente]);

        $this->db->insert('solicitudes_factura', [
            'id_cliente' => $idCliente,
            'id_venta' => $ticket['id_venta'],
            'estatus' => 'pendiente',
        ]);
        registrar_log('solicitar_factura_publica', 'venta', $ticket['id_venta'], "Folio: {$folio}, RFC: {$rfc}");

        $data = [
            'folio' => $folio,
            'pageTitle' => 'Solicitud Enviada',
        ];
        $this->view('factura_publica/confirmacion', $data);
    }

    public function pdf(array $params): void
    {
        $folio = $params['folio'];
        $ticketData = $this->ticket->getByFolio($folio);
        if (!$ticketData) {
            set_flash('error', 'Ticket no encontrado');
            $this->redirect('/factura');
        }

        $total = $ticketData['cantidad_vendida'] * $ticketData['precio_unitario'];
        $totalFmt = number_format($total, 2);
        $unitFmt = number_format($ticketData['precio_unitario'], 2);
        $folioS = safe_string($ticketData['folio_unico']);
        $cliente = safe_string($ticketData['razon_social'] ?? 'Consumidor Final');
        $rfc = safe_string($ticketData['rfc'] ?? '');
        $fecha = date('d/m/Y H:i', strtotime($ticketData['fecha_emision']));
        $producto = safe_string($ticketData['producto_nombre'] ?? 'N/A');
        $codigo = safe_string($ticketData['producto_codigo'] ?? '—');
        $moneda = safe_string($ticketData['moneda'] ?? 'MXN');
        $cantidad = (int) $ticketData['cantidad_vendida'];
        $estatus = $ticketData['estatus'] === 'emitido' ? 'Pagado' : 'Cancelado';
        $urlFactura = url('factura');

        $html = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket {$folioS}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #1a1a2e; padding: 30px; font-size: 11pt; }
        .header { text-align: center; border-bottom: 3px solid #1a1a2e; padding-bottom: 20px; margin-bottom: 25px; }
        .header h1 { font-size: 22pt; font-weight: 800; letter-spacing: 2px; }
        .header small { font-size: 8pt; color: #666; text-transform: uppercase; letter-spacing: 3px; }
        .folio-box { text-align: center; background: #f0f2f5; padding: 12px; border-radius: 8px; margin: 15px 0; }
        .folio-box .label { font-size: 7pt; text-transform: uppercase; letter-spacing: 2px; color: #888; }
        .folio-box .code { font-size: 18pt; font-weight: 800; letter-spacing: 2px; color: #0f3460; }
        .info-grid { display: flex; gap: 20px; margin-bottom: 25px; }
        .info-grid > div { flex: 1; background: #f8f9fa; padding: 12px 15px; border-radius: 6px; }
        .info-grid h6 { font-size: 7pt; text-transform: uppercase; letter-spacing: 1.5px; color: #999; margin-bottom: 4px; }
        .info-grid p { font-size: 10pt; font-weight: 700; }
        .info-grid .small { font-size: 8pt; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { font-size: 7pt; text-transform: uppercase; letter-spacing: 1.5px; padding: 8px 10px; background: #1a1a2e; color: #fff; text-align: left; }
        th.text-right { text-align: right; }
        th.text-center { text-align: center; }
        td { padding: 10px; font-size: 9pt; border-bottom: 1px solid #eee; }
        td.text-right { text-align: right; }
        td.text-center { text-align: center; }
        .totals { width: 260px; margin-left: auto; }
        .totals .row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 9pt; }
        .totals .grand { border-top: 2px solid #1a1a2e; margin-top: 4px; padding-top: 8px; font-size: 13pt; font-weight: 800; }
        .ref-box { margin-top: 25px; padding: 18px; border: 2px dashed #667eea80; border-radius: 8px; text-align: center; background: #f8f9ff; }
        .ref-box .label { font-size: 7pt; text-transform: uppercase; letter-spacing: 2px; color: #888; }
        .ref-box .code { font-size: 16pt; font-weight: 800; letter-spacing: 2px; color: #0f3460; font-family: monospace; }
        .ref-box .hint { font-size: 7pt; color: #999; margin-top: 4px; }
        .footer { text-align: center; font-size: 7pt; color: #aaa; border-top: 1px solid #eee; padding-top: 15px; margin-top: 25px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PLASTI FRUS</h1>
        <small>Sistema de Gesti&oacute;n de Producci&oacute;n</small>
    </div>

    <div class="folio-box">
        <div class="label">Comprobante de Venta</div>
        <div class="code">#{$folioS}</div>
    </div>

    <div class="info-grid">
        <div>
            <h6>Cliente</h6>
            <p>{$cliente}</p>
            <span class="small">RFC: {$rfc}</span>
        </div>
        <div>
            <h6>Emisi&oacute;n</h6>
            <p>{$fecha}</p>
            <span class="small">Estatus: {$estatus}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>C&oacute;digo</th>
                <th class="text-center">Cant.</th>
                <th class="text-right">P. Unitario</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>{$producto}</strong></td>
                <td>{$codigo}</td>
                <td class="text-center">{$cantidad}</td>
                <td class="text-right">\${$unitFmt} {$moneda}</td>
                <td class="text-right"><strong>\${$totalFmt} {$moneda}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="totals">
        <div class="row"><span>Subtotal</span><span>\${$totalFmt} {$moneda}</span></div>
        <div class="row"><span>IVA (0%)</span><span>\$0.00 {$moneda}</span></div>
        <div class="row grand"><span>TOTAL</span><span>\${$totalFmt} {$moneda}</span></div>
    </div>

    <div class="ref-box">
        <div class="label">C&oacute;digo de Facturaci&oacute;n</div>
        <div class="code">{$folioS}</div>
        <div class="hint">Ingresa este c&oacute;digo en {$urlFactura} para solicitar tu factura electr&oacute;nica.</div>
    </div>

    <div class="footer">
        Plasti Frus &mdash; Sistema de Gesti&oacute;n de Producci&oacute;n v1.0<br>
        Este documento es un comprobante interno. Para efectos fiscales, solicita tu factura electr&oacute;nica.
    </div>
</body>
</html>
HTML;

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("ticket_{$folioS}.pdf", ['Attachment' => true]);
        exit;
    }
}
