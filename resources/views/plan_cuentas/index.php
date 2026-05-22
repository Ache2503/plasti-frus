<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-journal-text"></i> Plan de Cuentas</h1>
    <?php if (contabilidad_permiso('crear')): ?>
    <a href="<?= url('contabilidad/plan-cuentas/create') ?>" class="btn btn-sm btn-dark"><i class="bi bi-plus-lg"></i> Nueva Cuenta</a>
    <?php endif; ?>
</div>

<form method="get" class="row g-2 mb-3">
    <div class="col-auto">
        <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Buscar código o nombre..." value="<?= safe_string($filtro_buscar ?? '') ?>">
    </div>
    <div class="col-auto">
        <select name="tipo" class="form-select form-select-sm">
            <option value="">Todos los tipos</option>
            <?php foreach (['activo','pasivo','capital','ingreso','gasto'] as $t): ?>
            <option value="<?= $t ?>" <?= ($filtro_tipo ?? '') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-sm btn-dark"><i class="bi bi-search"></i> Buscar</button>
        <a href="<?= url('contabilidad/plan-cuentas') ?>" class="btn btn-sm btn-outline-secondary">Limpiar</a>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 datatable">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Nivel</th>
                        <th>Naturaleza</th>
                        <th>Cuenta Padre</th>
                        <th>Activo</th>
                        <th class="no-sort">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cuentas as $c): ?>
                    <tr>
                        <td><a href="<?= url('contabilidad/libro-mayor/' . $c['id_cuenta']) ?>" title="Ver libro mayor"><code><?= safe_string($c['codigo']) ?></code></a></td>
                        <td><?= safe_string($c['nombre']) ?></td>
                        <td><span class="badge bg-<?= $c['tipo'] === 'activo' ? 'primary' : ($c['tipo'] === 'pasivo' ? 'warning text-dark' : ($c['tipo'] === 'capital' ? 'dark' : ($c['tipo'] === 'ingreso' ? 'success' : 'danger'))) ?>"><?= safe_string($c['tipo']) ?></span></td>
                        <td><?= $c['nivel'] ?></td>
                        <td><?= $c['naturaleza'] === 'deudora' ? 'Deudora' : 'Acreedora' ?></td>
                        <td><?= safe_string($c['padre_nombre'] ?? '') ?></td>
                        <td><?= $c['activo'] ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                        <td>
                            <?php if (contabilidad_permiso('editar')): ?>
                            <a href="<?= url('contabilidad/plan-cuentas/edit/' . $c['id_cuenta']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <?php endif; ?>
                            <a href="<?= url('contabilidad/libro-mayor/' . $c['id_cuenta']) ?>" class="btn btn-sm btn-outline-info" title="Libro Mayor"><i class="bi bi-book"></i></a>
                            <?php if (contabilidad_permiso('eliminar')): ?>
                            <form method="post" action="<?= url('contabilidad/plan-cuentas/delete/' . $c['id_cuenta']) ?>" class="d-inline" onsubmit="return confirm('¿Eliminar cuenta <?= safe_string($c['codigo']) ?>?')">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($cuentas)): ?>
                    <tr><td colspan="8" class="text-center text-muted">Sin resultados</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
