<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-clipboard-data"></i> Registro de Auditor&iacute;a</h1>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="<?= url('admin/logs') ?>" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small">Acci&oacute;n</label>
                <select name="accion" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <?php foreach ($actions as $a): ?>
                    <option value="<?= safe_string($a['accion']) ?>" <?= ($filters['accion'] ?? '') === $a['accion'] ? 'selected' : '' ?>><?= safe_string($a['accion']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Entidad</label>
                <select name="entidad" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <?php foreach ($entities as $e): ?>
                    <option value="<?= safe_string($e['entidad']) ?>" <?= ($filters['entidad'] ?? '') === $e['entidad'] ? 'selected' : '' ?>><?= safe_string($e['entidad']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Usuario</label>
                <input type="text" name="usuario" class="form-control form-control-sm" placeholder="Buscar..." value="<?= safe_string($filters['usuario'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Desde</label>
                <input type="date" name="fecha_desde" class="form-control form-control-sm" value="<?= safe_string($filters['fecha_desde'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="<?= safe_string($filters['fecha_hasta'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-dark w-100"><i class="bi bi-search"></i> Filtrar</button>
            </div>
        </form>
    </div>
</div>

<?php if (empty($logs)): ?>
<div class="alert alert-info text-center py-4">
    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
    No hay registros de auditor&iacute;a.
</div>
<?php else: ?>
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Fecha/Hora</th>
                        <th>Usuario</th>
                        <th>Acci&oacute;n</th>
                        <th>Entidad</th>
                        <th>ID</th>
                        <th>Detalle</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td class="text-nowrap small"><?= safe_string($log['created_at']) ?></td>
                        <td><?= safe_string($log['usuario_nombre']) ?></td>
                        <td>
                            <?php
                            $badge = match ($log['accion']) {
                                'INSERT', 'LOGIN' => 'bg-success',
                                'UPDATE' => 'bg-primary',
                                'DELETE', 'LOGOUT' => 'bg-danger',
                                'EXPORT' => 'bg-warning text-dark',
                                default => 'bg-secondary',
                            };
                            ?>
                            <span class="badge <?= $badge ?>"><?= safe_string($log['accion_label'] ?? $log['accion']) ?></span>
                        </td>
                        <td><?= safe_string($log['entidad']) ?></td>
                        <td><?= safe_string($log['entidad_id'] ?? '') ?></td>
                        <td class="small" style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= safe_string($log['detalle'] ?? '') ?>"><?= safe_string($log['detalle'] ?? '') ?></td>
                        <td class="small text-muted"><?= safe_string($log['ip'] ?? '') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../../partials/pagination.php'; ?>
<?php endif; ?>
