<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-file-earmark-text"></i> Póliza <?= safe_string($poliza['folio']) ?></h1>
    <div class="d-flex gap-2">
        <a href="<?= url('contabilidad/polizas') ?>" class="btn btn-sm btn-outline-dark"><i class="bi bi-arrow-left"></i> Volver</a>
        <?php if ($poliza['estatus'] === 'activo' && contabilidad_permiso('cancelar')): ?>
        <form method="post" action="<?= url('contabilidad/polizas/cancelar/' . $poliza['id_poliza']) ?>" class="d-inline" onsubmit="return confirm('¿Cancelar esta póliza?')">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle"></i> Cancelar</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <span><i class="bi bi-list-columns"></i> Partidas</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr><th>Cuenta</th><th>Concepto</th><th class="text-end">Cargo</th><th class="text-end">Abono</th></tr>
                        </thead>
                        <tbody>
                            <?php $totalCargo = 0; $totalAbono = 0; ?>
                            <?php foreach ($detalles as $d): ?>
                            <?php $totalCargo += $d['cargo']; $totalAbono += $d['abono']; ?>
                            <tr>
                                <td><code><?= safe_string($d['codigo']) ?></code> <?= safe_string($d['cuenta_nombre']) ?></td>
                                <td><?= safe_string($d['concepto'] ?? '') ?></td>
                                <td class="text-end"><?= $d['cargo'] > 0 ? format_money($d['cargo']) : '' ?></td>
                                <td class="text-end"><?= $d['abono'] > 0 ? format_money($d['abono']) : '' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="2" class="text-end">Totales:</td>
                                <td class="text-end"><?= format_money($totalCargo) ?></td>
                                <td class="text-end"><?= format_money($totalAbono) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white"><i class="bi bi-info-circle"></i> Detalles</div>
            <div class="card-body">
                <div class="mb-2"><strong>Folio:</strong> <?= safe_string($poliza['folio']) ?></div>
                <div class="mb-2"><strong>Fecha:</strong> <?= format_date($poliza['fecha']) ?></div>
                <div class="mb-2"><strong>Tipo:</strong> <span class="badge bg-<?= $poliza['tipo'] === 'ingreso' ? 'success' : ($poliza['tipo'] === 'egreso' ? 'danger' : 'info') ?>"><?= safe_string($poliza['tipo']) ?></span></div>
                <div class="mb-2"><strong>Estatus:</strong> <?= $poliza['estatus'] === 'activo' ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Cancelado</span>' ?></div>
                <div class="mb-2"><strong>Registrada por:</strong> <?= safe_string($poliza['nombre_usuario'] ?? '') ?></div>
                <div class="mb-2"><strong>Creada:</strong> <?= format_datetime($poliza['created_at']) ?></div>
                <hr>
                <h6>Concepto:</h6>
                <p class="text-muted"><?= nl2br(safe_string($poliza['concepto'])) ?></p>
            </div>
        </div>
    </div>
</div>
