<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-calendar-check"></i> Periodos Contables</h1>
    <a href="<?= url('contabilidad') ?>" class="btn btn-sm btn-outline-dark"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 datatable">
                <thead>
                    <tr><th>Periodo</th><th>Inicio</th><th>Fin</th><th>Estatus</th><th>Cerrado por</th><th>Fecha Cierre</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($periodos as $p): ?>
                    <tr>
                        <td><?= str_pad($p['mes'], 2, '0', STR_PAD_LEFT) ?>/<?= $p['anio'] ?></td>
                        <td><?= format_date($p['fecha_inicio']) ?></td>
                        <td><?= format_date($p['fecha_fin']) ?></td>
                        <td>
                            <?php if ($p['cerrado']): ?>
                            <span class="badge bg-secondary">Cerrado</span>
                            <?php else: ?>
                            <span class="badge bg-success">Abierto</span>
                            <?php endif; ?>
                        </td>
                        <td><?= safe_string($p['nombre_usuario'] ?? '') ?></td>
                        <td><?= $p['fecha_cierre'] ? format_datetime($p['fecha_cierre']) : '' ?></td>
                        <td>
                            <?php if (contabilidad_permiso('cerrar_periodo')): ?>
                            <?php if ($p['cerrado']): ?>
                            <form method="post" action="<?= url('contabilidad/periodos/reabrir/' . $p['id_periodo']) ?>" class="d-inline" onsubmit="return confirm('¿Reabrir el periodo?')">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="btn btn-sm btn-outline-warning"><i class="bi bi-unlock"></i> Reabrir</button>
                            </form>
                            <?php else: ?>
                            <form method="post" action="<?= url('contabilidad/periodos/cerrar/' . $p['id_periodo']) ?>" class="d-inline" onsubmit="return confirm('¿Cerrar el periodo? No se podrán registrar pólizas en este periodo.')">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="btn btn-sm btn-outline-dark"><i class="bi bi-lock"></i> Cerrar</button>
                            </form>
                            <?php endif; ?>
                            <?php else: ?>
                            <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
