<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-headset"></i> Mis Tickets de Soporte</h1>
    <a href="<?= url('tickets/nuevo') ?>" class="btn btn-dark"><i class="bi bi-plus-lg"></i> Nuevo Ticket</a>
</div>

<?php if (empty($tickets)): ?>
<div class="text-center py-5">
    <i class="bi bi-ticket text-muted" style="font-size: 4rem;"></i>
    <h4 class="mt-3 text-muted">No tienes tickets abiertos</h4>
    <p class="text-muted">Si tienes alguna duda o problema, crea un ticket y te atenderemos.</p>
    <a href="<?= url('tickets/nuevo') ?>" class="btn btn-dark"><i class="bi bi-plus-lg"></i> Crear Ticket</a>
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
                        <th>Prioridad</th>
                        <th>Respuestas</th>
                        <th>Estatus</th>
                        <th>Creado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $t): ?>
                    <tr class="<?= $t['estatus'] === 'abierto' ? 'table-active' : '' ?>">
                        <td><?= $t['id_ticket'] ?></td>
                        <td><strong><?= safe_string($t['titulo']) ?></strong></td>
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
                        <td class="text-center"><?= (int) ($t['total_respuestas'] ?? 0) ?></td>
                        <td>
                            <?php if ($t['estatus'] === 'abierto'): ?>
                            <span class="badge bg-success">Abierto</span>
                            <?php elseif ($t['estatus'] === 'respondido'): ?>
                            <span class="badge bg-info">Respondido</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Cerrado</span>
                            <?php endif; ?>
                        </td>
                        <td><?= format_date($t['created_at']) ?></td>
                        <td>
                            <a href="<?= url('tickets/' . $t['id_ticket']) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Ver
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
