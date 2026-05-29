<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-headset"></i> Soporte - Tickets de Clientes</h1>
    <span class="badge bg-<?= $pendientes > 0 ? 'warning text-dark' : 'secondary' ?> fs-6">
        <?= $pendientes ?> pendiente(s)
    </span>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar por título o cliente..." value="<?= safe_string($filters['search']) ?>">
    </div>
    <div class="col-md-2">
        <select name="estatus" class="form-select form-select-sm">
            <option value="">Todos los estatus</option>
            <option value="abierto" <?= $filters['estatus'] === 'abierto' ? 'selected' : '' ?>>Abierto</option>
            <option value="respondido" <?= $filters['estatus'] === 'respondido' ? 'selected' : '' ?>>Respondido</option>
            <option value="cerrado" <?= $filters['estatus'] === 'cerrado' ? 'selected' : '' ?>>Cerrado</option>
        </select>
    </div>
    <div class="col-md-2">
        <select name="prioridad" class="form-select form-select-sm">
            <option value="">Todas las prioridades</option>
            <option value="urgente" <?= $filters['prioridad'] === 'urgente' ? 'selected' : '' ?>>Urgente</option>
            <option value="alta" <?= $filters['prioridad'] === 'alta' ? 'selected' : '' ?>>Alta</option>
            <option value="media" <?= $filters['prioridad'] === 'media' ? 'selected' : '' ?>>Media</option>
            <option value="baja" <?= $filters['prioridad'] === 'baja' ? 'selected' : '' ?>>Baja</option>
        </select>
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-sm btn-dark w-100"><i class="bi bi-funnel"></i> Filtrar</button>
    </div>
    <div class="col-md-2">
        <a href="<?= url('soporte') ?>" class="btn btn-sm btn-outline-secondary w-100"><i class="bi bi-x-circle"></i> Limpiar</a>
    </div>
</form>

<?php if (empty($tickets)): ?>
<div class="text-center py-5">
    <i class="bi bi-ticket text-muted" style="font-size: 4rem;"></i>
    <h4 class="mt-3 text-muted">No hay tickets</h4>
    <p class="text-muted">No se encontraron tickets con los filtros actuales.</p>
</div>
<?php else: ?>
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Título</th>
                        <th>Cliente / Usuario</th>
                        <th>Prioridad</th>
                        <th>Estatus</th>
                        <th>Asignado</th>
                        <th>Resp.</th>
                        <th>Creado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $t): ?>
                    <tr class="<?= $t['estatus'] === 'abierto' ? 'table-warning' : ($t['estatus'] === 'respondido' ? 'table-info' : '') ?>">
                        <td><?= $t['id_ticket'] ?></td>
                        <td><strong><?= safe_string($t['titulo']) ?></strong></td>
                        <td>
                            <strong><?= safe_string($t['cliente_razon'] ?? 'N/A') ?></strong>
                            <div class="small text-muted">
                                Usuario: <?= safe_string($t['usuario_creador'] ?? $t['usuario_cliente'] ?? 'Sin usuario') ?>
                            </div>
                            <?php if (!empty($t['cliente_correo']) || !empty($t['cliente_telefono'])): ?>
                            <div class="small text-muted">
                                <?= safe_string($t['cliente_correo'] ?? '') ?>
                                <?= !empty($t['cliente_telefono']) ? ' · ' . safe_string($t['cliente_telefono']) : '' ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($t['prioridad'] === 'urgente'): ?>
                            <span class="badge bg-danger">Urgente</span>
                            <?php elseif ($t['prioridad'] === 'alta'): ?>
                            <span class="badge bg-warning text-dark">Alta</span>
                            <?php elseif ($t['prioridad'] === 'media'): ?>
                            <span class="badge bg-info">Media</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Baja</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($t['estatus'] === 'abierto'): ?>
                            <span class="badge bg-success">Abierto</span>
                            <?php elseif ($t['estatus'] === 'respondido'): ?>
                            <span class="badge bg-info">Respondido</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Cerrado</span>
                            <?php endif; ?>
                        </td>
                        <td><?= safe_string($t['usuario_asignado'] ?? '—') ?></td>
                        <td class="text-center"><?= (int) ($t['total_respuestas'] ?? 0) ?></td>
                        <td><?= format_date($t['created_at']) ?></td>
                        <td>
                            <a href="<?= url('soporte/' . $t['id_ticket']) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
