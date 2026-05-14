<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-file-text"></i> Facturas</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= url('facturas/solicitudes') ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-inbox"></i> Solicitudes
        </a>
    </div>
</div>

<?php if (empty($facturas)): ?>
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> No hay facturas registradas.
</div>
<?php else: ?>
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Producto</th>
                        <th>Subtotal</th>
                        <th>IVA</th>
                        <th>Total</th>
                        <th>Emisión</th>
                        <th>Estatus</th>
                        <th>Contabilizada</th>
                        <th class="no-sort">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($facturas as $f): ?>
                    <tr>
                        <td><?= $f['id_factura'] ?></td>
                        <td><?= safe_string($f['razon_social'] ?? 'N/A') ?></td>
                        <td><?= safe_string($f['producto_nombre'] ?? 'N/A') ?></td>
                        <td><?= format_money($f['subtotal'] ?? 0) ?></td>
                        <td><?= format_money($f['iva'] ?? 0) ?></td>
                        <td><strong><?= format_money($f['monto_total']) ?></strong></td>
                        <td><?= format_date($f['fecha_emision']) ?></td>
                        <td>
                            <span class="badge bg-<?= $f['estatus'] === 'emitida' ? 'success' : ($f['estatus'] === 'cancelada' ? 'danger' : 'secondary') ?>">
                                <?= $f['estatus'] ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($f['contabilizada']): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Sí
                                </span>
                                <?php if ($f['poliza_folio']): ?>
                                <a href="<?= url('contabilidad/polizas/show/' . $f['id_poliza']) ?>" class="small ms-1">
                                    <?= $f['poliza_folio'] ?>
                                </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge bg-secondary">No</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!$f['contabilizada'] && $f['estatus'] === 'emitida'): ?>
                            <form method="POST" action="<?= url('facturas/contabilizar/' . $f['id_factura']) ?>" style="display:inline" onsubmit="return confirm('¿Generar póliza contable para esta factura?')">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-calculator"></i> Contabilizar
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
