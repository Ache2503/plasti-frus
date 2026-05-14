<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-tools"></i> Máquinas</h1>
    <a href="<?= url('maquinas/create') ?>" class="btn btn-sm btn-dark"><i class="bi bi-plus-lg"></i> Nueva Máquina</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable table-hover mb-0">
                <thead>
                    <tr><th>ID</th><th>Nombre</th><th>Modelo</th><th>Serie</th><th>Estatus</th><th class="no-sort">Acciones</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($maquinas as $m): ?>
                    <tr>
                        <td><?= $m['id_maquina'] ?></td>
                        <td><?= safe_string($m['nombre']) ?></td>
                        <td><?= safe_string($m['modelo']) ?></td>
                        <td><?= safe_string($m['numero_serie']) ?></td>
                        <td><span class="badge bg-<?= $m['estatus'] === 'activo' ? 'success' : 'secondary' ?>"><?= safe_string($m['estatus']) ?></span></td>
                        <td>
                            <a href="<?= url('maquinas/edit/' . $m['id_maquina']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <a href="<?= url('maquinas/delete/' . $m['id_maquina']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar máquina?')"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($maquinas)): ?>
                    <tr><td colspan="6" class="text-center text-muted">Sin máquinas registradas</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
