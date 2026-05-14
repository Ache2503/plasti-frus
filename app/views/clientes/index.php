<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-people"></i> <?= safe_string($pageTitle) ?></h1>
    <div class="d-flex gap-2">
        <a href="<?= url('clientes/create') ?>" class="btn btn-sm btn-dark"><i class="bi bi-plus-lg"></i> Nuevo Cliente</a>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-auto">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar por nombre, RFC, ciudad..." value="<?= safe_string($search) ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-dark"><i class="bi bi-search"></i></button>
                <?php if (!empty($search)): ?>
                <a href="<?= url('clientes') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </div>
            <div class="col-auto ms-auto">
                <small class="text-muted"><?= $total ?> cliente(s)</small>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable table-hover mb-0">
                <thead>
                    <tr><th>ID</th><th>Razón Social</th><th>RFC</th><th>Ciudad</th><th>Estado</th><th>Teléfono</th><th>Correo</th><th>Vendedor</th><th class="no-sort">Acciones</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $c): ?>
                    <tr>
                        <td><?= $c['id_cliente'] ?></td>
                        <td><?= safe_string($c['razon_social']) ?></td>
                        <td><?= safe_string($c['rfc']) ?></td>
                        <td><?= safe_string($c['ciudad']) ?></td>
                        <td><?= safe_string($c['estado']) ?></td>
                        <td><?= safe_string($c['telefono']) ?></td>
                        <td><?= safe_string($c['correo']) ?></td>
                        <td><?= safe_string($c['vendedor_nombre'] ?? '—') ?></td>
                        <td>
                            <a href="<?= url('clientes/show/' . $c['id_cliente']) ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                            <?php if (es_vendedor()): ?>
                                <?php if (empty($c['id_vendedor'])): ?>
                                <form method="POST" action="<?= url('clientes/reclamar/' . $c['id_cliente']) ?>" style="display:inline">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('¿Reclamar este cliente?')"><i class="bi bi-hand-index"></i> Reclamar</button>
                                </form>
                                <?php endif; ?>
                            <?php elseif (!es_contador()): ?>
                            <a href="<?= url('clientes/edit/' . $c['id_cliente']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="<?= url('clientes/delete/' . $c['id_cliente']) ?>" style="display:inline" onsubmit="return confirm('¿Eliminar cliente?')">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($clientes)): ?>
                    <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td class="text-center text-muted">Sin clientes registrados</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($totalPages > 1): ?>
    <div class="card-footer">
        <nav>
            <ul class="pagination pagination-sm mb-0 justify-content-center">
                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= url('clientes?page=' . ($currentPage - 1) . (!empty($search) ? '&search=' . urlencode($search) : '')) ?>">Anterior</a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                    <a class="page-link" href="<?= url('clientes?page=' . $i . (!empty($search) ? '&search=' . urlencode($search) : '')) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= url('clientes?page=' . ($currentPage + 1) . (!empty($search) ? '&search=' . urlencode($search) : '')) ?>">Siguiente</a>
                </li>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
