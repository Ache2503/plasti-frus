<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-wallet2"></i> Mi Cartera</h1>
    <div class="d-flex gap-2">
        <span class="badge bg-secondary fs-6"><?= safe_string($rol_nombre) ?></span>
    </div>
</div>

<!-- Stats -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="stat-card card-dark">
            <div class="stat-content">
                <div class="stat-number"><?= format_money($saldo_actual) ?></div>
                <div class="stat-label"><i class="bi bi-wallet2"></i> Saldo Actual</div>
            </div>
            <i class="bi bi-wallet2 stat-icon"></i>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="stat-card card-danger">
            <div class="stat-content">
                <div class="stat-number"><?= format_money($total_cargos) ?></div>
                <div class="stat-label"><i class="bi bi-cart-check"></i> Total Comprado</div>
            </div>
            <i class="bi bi-cart-check stat-icon"></i>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="stat-card card-success">
            <div class="stat-content">
                <div class="stat-number"><?= format_money($total_abonos) ?></div>
                <div class="stat-label"><i class="bi bi-cash-coin"></i> Total Pagado</div>
            </div>
            <i class="bi bi-cash-coin stat-icon"></i>
        </div>
    </div>
</div>

<!-- Movimientos + Resumen -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list-ol"></i> Movimientos</span>
                <span class="badge bg-light text-dark"><?= count($movimientos) ?> registro(s)</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($movimientos)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-wallet2 text-muted" style="font-size: 3rem;"></i>
                    <p class="mt-2 text-muted">No hay movimientos registrados.</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Concepto</th>
                                <th>Referencia</th>
                                <th class="text-end">Monto</th>
                                <th class="text-end">Saldo</th>
                                <th>Tipo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $bal = $saldo_actual;
                            $movs = array_reverse($movimientos);
                            foreach ($movs as $m):
                                $bal = $m['saldo_despues'] ?? 0;
                            ?>
                            <tr>
                                <td><?= format_datetime($m['fecha_movimiento']) ?></td>
                                <td>
                                    <span class="fw-semibold"><?= safe_string($m['concepto']) ?></span>
                                    <?php if (!empty($m['producto_nombre'])): ?>
                                    <br><small class="text-muted"><?= safe_string($m['producto_nombre']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-muted"><?= safe_string($m['referencia'] ?? '—') ?></small></td>
                                <td class="text-end fw-semibold <?= $m['tipo'] === 'cargo' ? 'text-danger' : 'text-success' ?>">
                                    <?= $m['tipo'] === 'cargo' ? '-' : '+' ?> <?= format_money($m['monto']) ?>
                                </td>
                                <td class="text-end"><?= format_money($bal) ?></td>
                                <td>
                                    <span class="badge bg-<?= $m['tipo'] === 'cargo' ? 'danger' : 'success' ?> bg-opacity-10 text-<?= $m['tipo'] === 'cargo' ? 'danger' : 'success' ?>">
                                        <?= $m['tipo'] === 'cargo' ? 'Cargo' : 'Abono' ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <i class="bi bi-building"></i> Cliente
            </div>
            <div class="card-body">
                <p class="mb-1"><strong><?= safe_string($cliente['razon_social'] ?? 'N/A') ?></strong></p>
                <?php if (!empty($cliente['rfc'])): ?>
                <small class="text-muted d-block">RFC: <?= safe_string($cliente['rfc']) ?></small>
                <?php endif; ?>
                <?php if (!empty($cliente['correo'])): ?>
                <small class="text-muted d-block"><?= safe_string($cliente['correo']) ?></small>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <i class="bi bi-pie-chart"></i> Resumen
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total comprado</span>
                    <span class="fw-semibold text-danger"><?= format_money($total_cargos) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total pagado</span>
                    <span class="fw-semibold text-success"><?= format_money($total_abonos) ?></span>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="fw-bold">Saldo actual</span>
                    <span class="fw-bold fs-5 <?= $saldo_actual > 0 ? 'text-danger' : 'text-success' ?>">
                        <?= format_money($saldo_actual) ?>
                    </span>
                </div>
                <?php if ($saldo_actual > 0): ?>
                <div class="alert alert-warning small mt-3 mb-0 py-2">
                    <i class="bi bi-info-circle"></i> Tienes un saldo pendiente de <?= format_money($saldo_actual) ?>.
                </div>
                <?php else: ?>
                <div class="alert alert-success small mt-3 mb-0 py-2">
                    <i class="bi bi-check-circle"></i> Tu cartera est&aacute; en ceros.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Gráfica de evolución de saldo -->
<?php
$chartLabels = [];
$chartData = [];
$movsChart = array_reverse($movimientos);
foreach ($movsChart as $m) {
    $chartLabels[] = "'" . format_date($m['fecha_movimiento'], 'd/m') . "'";
    $chartData[] = $m['saldo_despues'] ?? 0;
}
?>
<?php if (!empty($movimientos)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header" style="background: linear-gradient(135deg, #2c3e50 0%, #1a1a2e 100%); color: #fff;">
                <i class="bi bi-graph-up"></i> Evoluci&oacute;n del Saldo
            </div>
            <div class="card-body">
                <canvas id="saldoChart" style="max-height: 250px;"></canvas>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('saldoChart');
    if (ctx) {
        new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: [<?= implode(',', $chartLabels) ?>],
                datasets: [{
                    label: 'Saldo',
                    data: [<?= implode(',', $chartData) ?>],
                    borderColor: '#0f3460',
                    backgroundColor: 'rgba(15, 52, 96, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3,
                    pointBackgroundColor: '#0f3460',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) { return '$' + value.toLocaleString(); }
                        }
                    }
                }
            }
        });
    }
});
</script>
<?php endif; ?>

<!-- Tarjetas Registradas -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-credit-card"></i> Mis Tarjetas</span>
                <span class="badge bg-light text-primary"><?= count($tarjetas) ?> registrada(s)</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if (empty($tarjetas)): ?>
                    <div class="col-12 text-center py-3">
                        <i class="bi bi-credit-card text-muted" style="font-size: 2.5rem;"></i>
                        <p class="mt-2 text-muted">No tienes tarjetas registradas.</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($tarjetas as $t): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-dark border-secondary h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-info"><?= safe_string($t['tipo']) ?></span>
                                    <form method="POST" action="<?= url('cartera/tarjetas/eliminar/' . $t['id_tarjeta']) ?>" class="d-inline" onsubmit="return confirm('Eliminar esta tarjeta?')">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                                <p class="card-text font-monospace fs-5 mb-1"><?= safe_string($t['numero_enmascarado']) ?></p>
                                <p class="card-text small text-muted mb-0"><?= safe_string($t['titular']) ?></p>
                                <?php if (!empty($t['fecha_expiracion'])): ?>
                                <small class="text-muted">Vence: <?= format_date($t['fecha_expiracion'], 'm/Y') ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>

                    <div class="col-md-4 mb-3">
                        <div class="card border-dashed h-100">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center text-center py-4">
                                <i class="bi bi-plus-circle text-muted" style="font-size: 2rem;"></i>
                                <p class="mt-2 text-muted mb-3">Agregar nueva tarjeta</p>
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTarjeta">
                                    <i class="bi bi-plus"></i> Agregar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Referencias de Depósito -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <span><i class="bi bi-cash-stack"></i> Referencias de Dep&oacute;sito</span>
                <div>
                    <span class="badge bg-dark text-warning me-2"><?= count($referencias) ?> referencia(s)</span>
                    <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#modalReferencia">
                        <i class="bi bi-plus"></i> Generar
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($referencias)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-cash-stack text-muted" style="font-size: 3rem;"></i>
                    <p class="mt-2 text-muted">No hay referencias generadas.</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Referencia</th>
                                <th class="text-end">Monto sugerido</th>
                                <th>Vencimiento</th>
                                <th>Estatus</th>
                                <th>Acci&oacute;n</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($referencias as $r): ?>
                            <tr>
                                <td><?= format_datetime($r['created_at']) ?></td>
                                <td>
                                    <span class="fw-semibold font-monospace"><?= safe_string($r['referencia']) ?></span>
                                </td>
                                <td class="text-end"><?= $r['monto_sugerido'] ? format_money($r['monto_sugerido']) : '—' ?></td>
                                <td><?= format_date($r['fecha_vencimiento']) ?></td>
                                <td>
                                    <?php if ($r['estatus'] === 'pendiente'): ?>
                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                    <?php elseif ($r['estatus'] === 'pagado'): ?>
                                    <span class="badge bg-success">Pagado</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">Cancelado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($r['estatus'] === 'pendiente'): ?>
                                    <form method="POST" action="<?= url('cartera/referencias/cancelar/' . $r['id_deposito']) ?>" class="d-inline" onsubmit="return confirm('Cancelar esta referencia?')">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Cancelar referencia">
                                            <i class="bi bi-x-circle"></i> Cancelar
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Agregar Tarjeta -->
<div class="modal fade" id="modalTarjeta" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="<?= url('cartera/tarjetas/agregar') ?>">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="modal-header" style="background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%); color: #fff; border-bottom: none;">
                    <h5 class="modal-title"><i class="bi bi-credit-card"></i> Agregar Tarjeta</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="background: #f8f9fa;">
                    <div class="mb-3">
                        <label class="form-label" style="color: #1a1a2e; font-weight: 600;">Tipo de tarjeta</label>
                        <select name="tipo" class="form-select" required>
                            <option value="">Seleccionar...</option>
                            <?php foreach ($tipos_tarjeta as $tt): ?>
                            <option value="<?= safe_string($tt) ?>"><?= safe_string($tt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: #1a1a2e; font-weight: 600;">Titular</label>
                        <input type="text" name="titular" class="form-control" placeholder="Nombre del titular" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: #1a1a2e; font-weight: 600;">N&uacute;mero de tarjeta</label>
                        <input type="text" name="numero" class="form-control" placeholder="0000 0000 0000 0000" maxlength="19" inputmode="numeric" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: #1a1a2e; font-weight: 600;">Fecha de expiraci&oacute;n</label>
                        <input type="text" name="expiracion" class="form-control" placeholder="MM/AAAA" maxlength="7">
                        <small class="text-muted">Formato: MM/AAAA (ej. 12/2027)</small>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8f9fa; border-top: 1px solid #dee2e6;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">Cancelar</button>
                    <button type="submit" class="btn" style="background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%); color: #fff; border-radius: 8px; border: none;"><i class="bi bi-save"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Generar Referencia -->
<div class="modal fade" id="modalReferencia" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="<?= url('cartera/referencias/generar') ?>">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="modal-header" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: #fff; border-bottom: none;">
                    <h5 class="modal-title"><i class="bi bi-cash-stack"></i> Generar Referencia de Dep&oacute;sito</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="background: #f8f9fa;">
                    <p class="small" style="color: #555;">Genera una referencia &uacute;nica para depositar dinero a tu cartera. Puedes usarla para pagar en banco, transferencia o tienda de conveniencia.</p>
                    <div class="mb-3">
                        <label class="form-label" style="color: #1a1a2e; font-weight: 600;">Monto sugerido <small class="text-muted">(opcional)</small></label>
                        <div class="input-group">
                            <span class="input-group-text" style="background: #fff;">$</span>
                            <input type="number" name="monto" class="form-control" placeholder="0.00" step="0.01" min="0">
                        </div>
                        <small class="text-muted">Deja en blanco si no tienes un monto espec&iacute;fico.</small>
                    </div>
                    <div class="alert d-flex align-items-center gap-2 small py-2 mb-0" style="background: #e8f4fd; color: #0f3460; border: 1px solid #b8d4f0; border-radius: 8px;">
                        <i class="bi bi-info-circle" style="font-size: 1rem;"></i>
                        <span>La referencia tendr&aacute; una vigencia de 7 d&iacute;as.</span>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8f9fa; border-top: 1px solid #dee2e6;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">Cancelar</button>
                    <button type="submit" class="btn" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: #fff; border-radius: 8px; border: none;"><i class="bi bi-stars"></i> Generar Referencia</button>
                </div>
            </form>
        </div>
    </div>
</div>
