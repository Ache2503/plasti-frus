<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-bounding-box-circles"></i> Moldes</h1>
    <a href="<?= url('moldes/create') ?>" class="btn btn-sm btn-dark"><i class="bi bi-plus-lg"></i> Nuevo Molde</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable table-hover mb-0">
                <thead>
                    <tr><th>ID</th><th>Nombre</th><th>Cavidades</th><th>Material</th><th>Vida Útil</th><th>Ciclos Acum.</th><th>Estatus</th><th>CEDIS</th><th class="no-sort">Acciones</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($moldes as $m): ?>
                    <tr>
                        <td><?= $m['id_molde'] ?></td>
                        <td><?= safe_string($m['nombre_molde']) ?></td>
                        <td><?= $m['numero_cavidades'] ?></td>
                        <td><?= safe_string($m['material_molde']) ?></td>
                        <td><?= number_format($m['vida_util_golpes']) ?></td>
                        <td><?= number_format($m['ciclos_acumulados']) ?></td>
                        <td><span class="badge bg-<?= $m['estatus'] === 'activo' ? 'success' : 'secondary' ?>"><?= safe_string($m['estatus']) ?></span></td>
                        <td><?= safe_string($m['cede_nombre'] ?? 'N/A') ?></td>
                        <td>
                            <a href="<?= url('moldes/edit/' . $m['id_molde']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <a href="<?= url('moldes/delete/' . $m['id_molde']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar molde?')"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($moldes)): ?>
                    <tr><td colspan="9" class="text-center text-muted">Sin moldes registrados</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
