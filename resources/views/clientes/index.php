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
            <?php if (es_vendedor()): ?>
            <div class="col-auto">
                <select name="seguimiento" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="sin_seguimiento" <?= ($_GET['seguimiento'] ?? '') === 'sin_seguimiento' ? 'selected' : '' ?>>Sin seguimiento</option>
                    <option value="con_seguimiento" <?= ($_GET['seguimiento'] ?? '') === 'con_seguimiento' ? 'selected' : '' ?>>Con seguimiento reciente</option>
                </select>
            </div>
            <?php endif; ?>
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
                    <tr>
                        <th>ID</th><th>Razón Social</th><th>RFC</th><th>Ciudad</th><th>Teléfono</th><th>Correo</th>
                        <?php if (es_vendedor()): ?>
                        <th>Última Interacción</th>
                        <?php endif; ?>
                        <th>Vendedor</th><th class="no-sort">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $c): ?>
                    <tr>
                        <td><?= $c['id_cliente'] ?></td>
                        <td><?= safe_string($c['razon_social']) ?></td>
                        <td><?= safe_string($c['rfc']) ?></td>
                        <td><?= safe_string($c['ciudad']) ?></td>
                        <td><?= safe_string($c['telefono']) ?></td>
                        <td><?= safe_string($c['correo']) ?></td>
                        <?php if (es_vendedor()): ?>
                        <td style="font-size:0.8rem;">
                            <?php if (!empty($c['ultima_interaccion'])): ?>
                                <span class="badge bg-info"><?= format_date($c['ultima_interaccion']['fecha'], 'd/m/Y') ?></span>
                                <small class="d-block text-muted"><?= safe_string($c['ultima_interaccion']['tipo']) ?></small>
                            <?php else: ?>
                                <span class="badge bg-secondary">Sin seguimiento</span>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                        <td><?= safe_string($c['vendedor_nombre'] ?? '—') ?></td>
                        <td>
                            <a href="<?= url('clientes/show/' . $c['id_cliente']) ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                            <?php if (es_vendedor()): ?>
                                <button class="btn btn-sm btn-outline-secondary" onclick="verHistorial(<?= $c['id_cliente'] ?>, '<?= safe_string($c['razon_social']) ?>')"><i class="bi bi-clock-history"></i></button>
                                <?php if (empty($c['id_vendedor'])): ?>
                                <form method="POST" action="<?= url('clientes/reclamar/' . $c['id_cliente']) ?>" style="display:inline">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('¿Reclamar este cliente?')"><i class="bi bi-hand-index"></i></button>
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
                    <tr><td colspan="9" class="text-center text-muted">Sin clientes registrados</td></tr>
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

<!-- Modal Historial de Interacciones -->
<div class="modal fade" id="historialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-clock-history"></i> Historial: <span id="historialClienteNombre"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <button class="btn btn-sm btn-success" onclick="abrirNuevaInteraccion()"><i class="bi bi-plus-lg"></i> Nueva Interacción</button>
                </div>
                <div id="historialContenido">
                    <div class="text-center text-muted py-3">Cargando...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nueva Interacción -->
<div class="modal fade" id="nuevaInteraccionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="interaccionForm">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Interacción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="id_cliente" id="interaccion_cliente_id">
                    <div class="mb-2">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" class="form-select form-select-sm">
                            <option value="llamada">Llamada</option>
                            <option value="correo">Correo</option>
                            <option value="reunion">Reunión</option>
                            <option value="nota">Nota</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Fecha</label>
                        <input type="datetime-local" name="fecha" class="form-control form-control-sm" value="<?= date('Y-m-d\TH:i') ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-check-lg"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
window._clienteActual = 0;

function verHistorial(clienteId, nombre) {
    window._clienteActual = clienteId;
    document.getElementById('historialClienteNombre').textContent = nombre;
    document.getElementById('historialContenido').innerHTML = '<div class="text-center text-muted py-3">Cargando...</div>';
    new bootstrap.Modal(document.getElementById('historialModal')).show();
    fetch('<?= url('clientes/historial') ?>/' + clienteId)
        .then(r => r.json())
        .then(data => {
            let html = '';
            if (data.length === 0) {
                html = '<div class="text-center text-muted py-3">Sin interacciones registradas</div>';
            } else {
                html = '<div class="list-group">';
                data.forEach(i => {
                    html += '<div class="list-group-item py-2"><div class="d-flex justify-content-between"><small class="badge bg-secondary">' + i.tipo + '</small><small class="text-muted">' + i.fecha + '</small></div><div class="mt-1">' + i.descripcion + '</div><small class="text-muted">— ' + (i.vendedor_nombre || '') + '</small></div>';
                });
                html += '</div>';
            }
            document.getElementById('historialContenido').innerHTML = html;
        });
}

function abrirNuevaInteraccion() {
    document.getElementById('interaccion_cliente_id').value = window._clienteActual;
    bootstrap.Modal.getInstance(document.getElementById('historialModal')).hide();
    new bootstrap.Modal(document.getElementById('nuevaInteraccionModal')).show();
}

document.getElementById('interaccionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('<?= url('interacciones/store') ?>', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                bootstrap.Modal.getInstance(document.getElementById('nuevaInteraccionModal')).hide();
                verHistorial(window._clienteActual, document.getElementById('historialClienteNombre').textContent);
                document.getElementById('interaccionForm').reset();
            }
        });
});
</script>
