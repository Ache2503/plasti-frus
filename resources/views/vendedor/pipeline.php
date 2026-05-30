<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h4 mb-0"><i class="bi bi-funnel"></i> Pipeline de Ventas</h1>
    <div>
        <span class="badge bg-dark me-1" style="font-size:0.9rem;">Total: <?= format_money($total_pipeline) ?></span>
        <span class="badge bg-info" style="font-size:0.9rem;">Conversión: <?= $tasa_conversion['tasa'] ?>%</span>
        <button class="btn btn-sm btn-success ms-2" onclick="abrirModal(null)"><i class="bi bi-plus-lg"></i> Nueva Oportunidad</button>
    </div>
</div>

<div class="row g-2 mb-3">
    <div class="col-12">
        <form method="GET" class="row g-1 align-items-end">
            <div class="col-auto">
                <?php if (!es_vendedor()): ?>
                <select name="vendedor" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Todos los vendedores</option>
                    <?php foreach ($vendedores as $v): ?>
                    <option value="<?= $v['id_usuario'] ?>" <?= (string) ($filtro_vendedor ?? '') === (string) $v['id_usuario'] ? 'selected' : '' ?>>
                        <?= safe_string(trim(($v['nombre'] ?? '') . ' ' . ($v['apellido_paterno'] ?? '')) ?: $v['nombre_usuario']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
            </div>
            <div class="col-auto">
                <select name="etapa" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Todas las etapas</option>
                    <?php foreach ($etapas as $k => $v): ?>
                    <option value="<?= $k ?>" <?= ($filtro_etapa ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <input type="date" name="fecha_desde" class="form-control form-control-sm" placeholder="Desde" value="<?= safe_string($_GET['fecha_desde'] ?? '') ?>">
            </div>
            <div class="col-auto">
                <input type="date" name="fecha_hasta" class="form-control form-control-sm" placeholder="Hasta" value="<?= safe_string($_GET['fecha_hasta'] ?? '') ?>">
            </div>
            <div class="col-auto">
                <input type="number" name="valor_min" class="form-control form-control-sm" placeholder="Valor mín." step="0.01" value="<?= safe_string($_GET['valor_min'] ?? '') ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-funnel"></i> Filtrar</button>
                <a href="<?= url('pipeline') ?>" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="row g-2 flex-nowrap overflow-auto pb-2" style="min-height: 500px;">
    <?php
    $etapasColores = ['prospeccion' => 'secondary', 'contactado' => 'info', 'propuesta' => 'primary', 'negociacion' => 'warning', 'cerrado_ganado' => 'success', 'cerrado_perdido' => 'danger'];
    $oportunidadesPorEtapa = [];
    foreach ($oportunidades as $o) {
        $oportunidadesPorEtapa[$o['etapa']][] = $o;
    }
    ?>
    <?php foreach ($etapas as $etapaKey => $etapaLabel): ?>
    <div class="col" style="min-width: 250px;">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-<?= $etapasColores[$etapaKey] ?? 'secondary' ?> text-white py-1 px-2 d-flex justify-content-between align-items-center">
                <small class="fw-bold"><?= $etapaLabel ?></small>
                <span class="badge bg-light text-dark"><?= count($oportunidadesPorEtapa[$etapaKey] ?? []) ?></span>
            </div>
            <div class="card-body p-1" style="background: #f8f9fa;">
                <div class="kanban-column" data-etapa="<?= $etapaKey ?>" style="min-height: 200px;">
                    <?php foreach ($oportunidadesPorEtapa[$etapaKey] ?? [] as $opp): ?>
                    <div class="card mb-1 kanban-card" data-id="<?= $opp['id_oportunidad'] ?>" style="cursor: pointer; font-size:0.8rem;" draggable="true">
                        <div class="card-body p-2" onclick="editarOportunidad(<?= $opp['id_oportunidad'] ?>)">
                            <div class="fw-bold"><?= safe_string($opp['titulo']) ?></div>
                            <div><small class="text-muted"><?= safe_string($opp['cliente_nombre'] ?? 'Sin cliente') ?></small></div>
                            <div class="d-flex justify-content-between mt-1">
                                <span class="fw-bold text-success"><?= format_money($opp['valor']) ?></span>
                                <small class="text-muted"><?= $opp['probabilidad'] ?>%</small>
                            </div>
                            <?php if ($opp['fecha_cierre_estimada']): ?>
                            <small class="text-muted"><i class="bi bi-calendar"></i> <?= format_date($opp['fecha_cierre_estimada']) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($oportunidadesPorEtapa[$etapaKey] ?? [])): ?>
                    <div class="text-center text-muted py-4" style="font-size:0.8rem;">Sin oportunidades</div>
                    <?php endif; ?>
                </div>
                <button class="btn btn-sm btn-outline-success w-100 mt-1" onclick="abrirModal('<?= $etapaKey ?>')"><i class="bi bi-plus"></i></button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Modal Crear/Editar Oportunidad -->
<div class="modal fade" id="oportunidadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="oportunidadForm" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nueva Oportunidad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="id_oportunidad" id="id_oportunidad">
                    <div class="mb-2">
                        <label class="form-label">Título</label>
                        <input type="text" name="titulo" id="opp_titulo" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Cliente</label>
                        <select name="id_cliente" id="opp_cliente" class="form-select form-select-sm">
                            <option value="">Sin cliente</option>
                            <?php foreach ($clientes as $c): ?>
                            <option value="<?= $c['id_cliente'] ?>" data-vendedor="<?= safe_string($c['id_vendedor'] ?? '') ?>"><?= safe_string($c['razon_social']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if (!es_vendedor()): ?>
                    <div class="mb-2">
                        <label class="form-label">Vendedor</label>
                        <select name="id_vendedor" id="opp_vendedor" class="form-select form-select-sm" required>
                            <option value="">Seleccionar vendedor</option>
                            <?php foreach ($vendedores as $v): ?>
                            <option value="<?= $v['id_usuario'] ?>"><?= safe_string(trim(($v['nombre'] ?? '') . ' ' . ($v['apellido_paterno'] ?? '')) ?: $v['nombre_usuario']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="row mb-2">
                        <div class="col-6">
                            <label class="form-label">Valor</label>
                            <input type="number" name="valor" id="opp_valor" class="form-control form-control-sm" step="0.01">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Probabilidad (%)</label>
                            <input type="number" name="probabilidad" id="opp_probabilidad" class="form-control form-control-sm" min="0" max="100">
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">
                            <label class="form-label">Etapa</label>
                            <select name="etapa" id="opp_etapa" class="form-select form-select-sm">
                                <?php foreach ($etapas as $k => $v): ?>
                                <option value="<?= $k ?>"><?= $v ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Fecha cierre est.</label>
                            <input type="date" name="fecha_cierre_estimada" id="opp_fecha" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Notas</label>
                        <textarea name="notas" id="opp_notas" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-danger" onclick="eliminarOportunidad()" id="btnEliminar" style="display:none"><i class="bi bi-trash"></i></button>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-check-lg"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.querySelectorAll('.kanban-column').forEach(col => {
    new Sortable(col, {
        group: 'pipeline',
        animation: 150,
        onEnd: function(evt) {
            const card = evt.item;
            const id = card.dataset.id;
            const nuevaEtapa = evt.to.dataset.etapa;
            const formData = new FormData();
            formData.append('csrf_token', '<?= csrf_token() ?>');
            formData.append('etapa', nuevaEtapa);
            fetch('<?= url('pipeline/etapa') ?>/' + id, { method: 'POST', body: formData, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(d => { if (!d.success) location.reload(); });
        }
    });
});

function abrirModal(etapa) {
    document.getElementById('modalTitle').textContent = 'Nueva Oportunidad';
    document.getElementById('id_oportunidad').value = '';
    document.getElementById('opp_titulo').value = '';
    document.getElementById('opp_cliente').value = '';
    if (document.getElementById('opp_vendedor')) {
        document.getElementById('opp_vendedor').value = '<?= safe_string($filtro_vendedor ?? '') ?>';
    }
    document.getElementById('opp_valor').value = '';
    document.getElementById('opp_probabilidad').value = '';
    document.getElementById('opp_etapa').value = etapa || 'prospeccion';
    document.getElementById('opp_fecha').value = '';
    document.getElementById('opp_notas').value = '';
    document.getElementById('btnEliminar').style.display = 'none';
    document.getElementById('oportunidadForm').action = '<?= url('pipeline/store') ?>';
    new bootstrap.Modal(document.getElementById('oportunidadModal')).show();
}

function editarOportunidad(id) {
    fetch('<?= url('pipeline/data' . (!empty($filtro_vendedor) ? '?vendedor=' . urlencode((string) $filtro_vendedor) : '')) ?>', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            const opp = data.find(o => o.id_oportunidad == id);
            if (!opp) return;
            document.getElementById('modalTitle').textContent = 'Editar Oportunidad';
            document.getElementById('id_oportunidad').value = opp.id_oportunidad;
            document.getElementById('opp_titulo').value = opp.titulo;
            document.getElementById('opp_cliente').value = opp.id_cliente || '';
            if (document.getElementById('opp_vendedor')) {
                document.getElementById('opp_vendedor').value = opp.id_vendedor || '';
            }
            document.getElementById('opp_valor').value = opp.valor;
            document.getElementById('opp_probabilidad').value = opp.probabilidad;
            document.getElementById('opp_etapa').value = opp.etapa;
            document.getElementById('opp_fecha').value = opp.fecha_cierre_estimada || '';
            document.getElementById('opp_notas').value = opp.notas || '';
            document.getElementById('btnEliminar').style.display = 'inline-block';
            document.getElementById('oportunidadForm').action = '<?= url('pipeline/update') ?>/' + id;
            new bootstrap.Modal(document.getElementById('oportunidadModal')).show();
        });
}

function eliminarOportunidad() {
    if (!confirm('¿Eliminar oportunidad?')) return;
    const id = document.getElementById('id_oportunidad').value;
    const formData = new FormData();
    formData.append('csrf_token', '<?= csrf_token() ?>');
    fetch('<?= url('pipeline/delete') ?>/' + id, { method: 'POST', body: formData, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(d => { if (d.success) location.reload(); });
}

document.getElementById('opp_etapa').addEventListener('change', function() {
    const probs = { prospeccion: 10, contactado: 25, propuesta: 50, negociacion: 75, cerrado_ganado: 100, cerrado_perdido: 0 };
    if (!document.getElementById('opp_probabilidad').value) {
        document.getElementById('opp_probabilidad').value = probs[this.value] || 0;
    }
});
</script>
