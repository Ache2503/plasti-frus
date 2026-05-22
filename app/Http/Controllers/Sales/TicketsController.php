<?php
namespace App\Http\Controllers\Sales;

use App\Core\Controller;
use App\Models\Ticket;

class TicketsController extends Controller
{
    private $ticket;

    public function __construct()
    {
        $this->ticket = new Ticket();
    }

    public function show(array $params): void
    {
        $folio = $params['folio'];
        $ticket = $this->ticket->getByFolio($folio);
        if (!$ticket) {
            set_flash('error', 'Ticket no encontrado');
            $this->redirect('/');
        }
        $data = [
            'ticket' => $ticket,
            'pageTitle' => 'Ticket ' . $folio,
        ];
        $this->view('tickets/show', $data);
    }

    public function pdf(array $params): void
    {
        $folio = $params['folio'];
        $ticketData = $this->ticket->getByFolio($folio);
        if (!$ticketData) {
            set_flash('error', 'Ticket no encontrado');
            $this->redirect('/');
        }

        $html = $this->renderInvoiceHtml($ticketData);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("ticket_{$folio}.pdf", ['Attachment' => true]);
        exit;
    }

    private function renderInvoiceHtml(array $ticket): string
    {
        $total = $ticket['cantidad_vendida'] * $ticket['precio_unitario'];
        $totalFmt = number_format($total, 2);
        $unitFmt = number_format($ticket['precio_unitario'], 2);
        $folio = safe_string($ticket['folio_unico']);
        $cliente = safe_string($ticket['razon_social'] ?? 'Consumidor Final');
        $rfc = safe_string($ticket['rfc'] ?? '');
        $fecha = date('d/m/Y H:i', strtotime($ticket['fecha_emision']));
        $producto = safe_string($ticket['producto_nombre'] ?? 'N/A');
        $codigo = safe_string($ticket['producto_codigo'] ?? '—');
        $moneda = safe_string($ticket['moneda'] ?? 'MXN');
        $cantidad = (int) $ticket['cantidad_vendida'];
        $estatus = $ticket['estatus'] === 'emitido' ? 'Pagado' : 'Cancelado';
        $urlFactura = url('factura');

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket {$folio}</title>
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
        <div class="label">Ticket</div>
        <div class="code">#{$folio}</div>
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
        <div class="code">{$folio}</div>
        <div class="hint">Ingresa este c&oacute;digo en {$urlFactura} para solicitar tu factura electr&oacute;nica.</div>
    </div>

    <div class="footer">
        Plasti Frus &mdash; Sistema de Gesti&oacute;n de Producci&oacute;n v1.0<br>
        Este documento es un comprobante interno. Para efectos fiscales, solicita tu factura electr&oacute;nica.
    </div>
</body>
</html>
HTML;
    }
}