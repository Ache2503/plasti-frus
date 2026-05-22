<style>
    .invoice-wrap { max-width: 800px; margin: 0 auto; }
    .invoice { background: #fff; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,.1); overflow: hidden; }
    .invoice-header { background: linear-gradient(135deg, #1a1a2e, #0f3460); color: #fff; padding: 30px 35px; display: flex; justify-content: space-between; align-items: center; }
    .invoice-header h2 { font-weight: 800; font-size: 1.4rem; margin: 0; letter-spacing: 1px; }
    .invoice-header small { opacity: .7; font-size: .75rem; letter-spacing: 2px; text-transform: uppercase; }
    .invoice-badge { background: rgba(255,255,255,.15); padding: 8px 18px; border-radius: 8px; text-align: center; }
    .invoice-badge .folio { font-size: 1.1rem; font-weight: 700; letter-spacing: 1px; }
    .invoice-badge .label { font-size: .6rem; text-transform: uppercase; letter-spacing: 2px; opacity: .6; }
    .invoice-body { padding: 35px; }
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
    .info-box { background: #f8f9fa; border-radius: 10px; padding: 15px 18px; }
    .info-box h6 { font-size: .65rem; text-transform: uppercase; letter-spacing: 1.5px; color: #999; margin-bottom: 6px; font-weight: 600; }
    .info-box p { font-size: .9rem; font-weight: 600; margin: 0; color: #1a1a2e; }
    .info-box .small-text { font-size: .8rem; font-weight: 400; color: #666; }
    .inv-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
    .inv-table th { font-size: .65rem; text-transform: uppercase; letter-spacing: 1.5px; padding: 10px 12px; background: #1a1a2e; color: #fff; font-weight: 600; }
    .inv-table td { padding: 12px; font-size: .85rem; border-bottom: 1px solid #eee; }
    .inv-table tbody tr:last-child td { border-bottom: none; }
    .invoice-totals { margin-left: auto; width: 280px; }
    .total-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: .85rem; }
    .total-row.grand { border-top: 2px solid #1a1a2e; margin-top: 6px; padding-top: 10px; font-size: 1.15rem; font-weight: 800; color: #1a1a2e; }
    .invoice-ref { margin-top: 30px; padding: 20px; background: linear-gradient(135deg, #667eea15, #764ba215); border: 2px dashed #667eea40; border-radius: 12px; text-align: center; }
    .invoice-ref .ref-label { font-size: .7rem; text-transform: uppercase; letter-spacing: 2px; color: #666; margin-bottom: 4px; }
    .invoice-ref .ref-code { font-size: 1.6rem; font-weight: 800; color: #0f3460; letter-spacing: 3px; font-family: 'Courier New', monospace; }
    .invoice-ref .ref-hint { font-size: .75rem; color: #888; margin-top: 6px; }
    .invoice-footer { text-align: center; padding: 20px 35px; border-top: 1px solid #eee; font-size: .75rem; color: #999; }
    @media (max-width: 600px) { .invoice-header { flex-direction: column; text-align: center; gap: 12px; } .info-grid { grid-template-columns: 1fr; } .invoice-totals { width: 100%; } }
    @media print { .invoice { box-shadow: none; border-radius: 0; } .no-print { display: none !important; } @page { margin: 10mm; } }
</style>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom no-print">
    <h1 class="h2"><i class="bi bi-receipt"></i> Ticket #<?= safe_string($ticket['folio_unico']) ?></h1>
    <div class="d-flex gap-2">
        <a href="<?= url('tickets/' . $ticket['folio_unico'] . '/pdf') ?>" class="btn btn-dark btn-sm"><i class="bi bi-download"></i> Descargar PDF</a>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer"></i> Imprimir</button>
        <button onclick="history.back()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Volver</button>
    </div>
</div>

<div class="invoice-wrap">
    <div class="invoice">
        <div class="invoice-header">
            <div>
                <h2>PLASTI FRUS</h2>
                <small>Sistema de Gesti&oacute;n de Producci&oacute;n</small>
            </div>
            <div class="invoice-badge">
                <div class="label">Ticket</div>
                <div class="folio">#<?= safe_string($ticket['folio_unico']) ?></div>
            </div>
        </div>

        <div class="invoice-body">
            <div class="info-grid">
                <div class="info-box">
                    <h6>Cliente</h6>
                    <p><?= safe_string($ticket['razon_social'] ?? 'Consumidor Final') ?></p>
                    <?php if (!empty($ticket['rfc'])): ?>
                    <span class="small-text">RFC: <?= safe_string($ticket['rfc']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="info-box">
                    <h6>Datos de Emisi&oacute;n</h6>
                    <p>Fecha: <?= format_datetime($ticket['fecha_emision']) ?></p>
                    <span class="small-text">
                        Estatus: 
                        <span class="badge bg-<?= $ticket['estatus'] === 'emitido' ? 'success' : 'secondary' ?>">
                            <?= $ticket['estatus'] === 'emitido' ? 'Pagado' : 'Cancelado' ?>
                        </span>
                    </span>
                </div>
            </div>

            <table class="inv-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>C&oacute;digo</th>
                        <th class="text-center">Cant.</th>
                        <th class="text-end">P. Unitario</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong><?= safe_string($ticket['producto_nombre'] ?? 'N/A') ?></strong></td>
                        <td><?= safe_string($ticket['producto_codigo'] ?? '&mdash;') ?></td>
                        <td class="text-center"><?= $ticket['cantidad_vendida'] ?></td>
                        <td class="text-end"><?= format_money($ticket['precio_unitario'], $ticket['moneda']) ?></td>
                        <td class="text-end"><strong><?= format_money($ticket['cantidad_vendida'] * $ticket['precio_unitario'], $ticket['moneda']) ?></strong></td>
                    </tr>
                </tbody>
            </table>

            <div class="invoice-totals ms-auto">
                <div class="total-row">
                    <span>Subtotal</span>
                    <span><?= format_money($ticket['cantidad_vendida'] * $ticket['precio_unitario'], $ticket['moneda']) ?></span>
                </div>
                <div class="total-row">
                    <span>IVA (0%)</span>
                    <span>$0.00 MXN</span>
                </div>
                <div class="total-row grand">
                    <span>TOTAL</span>
                    <span><?= format_money($ticket['cantidad_vendida'] * $ticket['precio_unitario'], $ticket['moneda']) ?></span>
                </div>
            </div>

            <div class="invoice-ref">
                <div class="ref-label">C&oacute;digo de Facturaci&oacute;n</div>
                <div class="ref-code"><?= safe_string($ticket['folio_unico']) ?></div>
                <div class="ref-hint">
                    <i class="bi bi-info-circle"></i>
                    Usa este c&oacute;digo en <a href="<?= url('factura') ?>" class="fw-semibold"><?= url('factura') ?></a> para solicitar tu factura electr&oacute;nica sin necesidad de iniciar sesi&oacute;n.
                </div>
            </div>

            <?php if ($ticket['estatus'] === 'emitido' && ($ticket['venta_estatus'] ?? '') === 'completado'): ?>
            <div class="text-center mt-3 no-print">
                <a href="<?= url('factura/solicitar/' . $ticket['folio_unico']) ?>" class="btn btn-dark btn-lg w-100">
                    <i class="bi bi-receipt"></i> Solicitar Factura Electr&oacute;nica
                </a>
            </div>
            <?php endif; ?>
        </div>

        <div class="invoice-footer">
            <i class="bi bi-gear-fill"></i> Plasti Frus &mdash; Sistema de Gesti&oacute;n de Producci&oacute;n v1.0<br>
            Este documento es un comprobante interno. Para efectos fiscales, solicita tu factura electr&oacute;nica.
        </div>
    </div>
</div>
