<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h4 mb-0"><i class="bi bi-calendar"></i> Mi Agenda</h1>
    <button class="btn btn-sm btn-success" onclick="abrirModalActividad(null)"><i class="bi bi-plus-lg"></i> Nueva Actividad</button>
</div>

<div class="row g-2 mb-3">
    <div class="col-auto">
        <a href="<?= url('agenda?mes=' . date('Y-m', strtotime($mes . '-01 -1 month'))) ?>" class="btn btn-sm btn-outline-secondary">&laquo; Mes Anterior</a>
    </div>
    <div class="col-auto">
        <h5 class="mb-0"><?= format_date($mes . '-01', 'F Y') ?></h5>
    </div>
    <div class="col-auto">
        <a href="<?= url('agenda?mes=' . date('Y-m', strtotime($mes . '-01 +1 month'))) ?>" class="btn btn-sm btn-outline-secondary">Mes Siguiente &raquo;</a>
    </div>
    <div class="col-auto">
        <a href="<?= url('agenda') ?>" class="btn btn-sm btn-outline-primary">Hoy</a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-2">
        <table class="table table-bordered mb-0" style="font-size:0.8rem;">
            <thead>
                <tr class="text-center">
                    <th style="width:14.28%">Domingo</th><th style="width:14.28%">Lunes</th><th style="width:14.28%">Martes</th>
                    <th style="width:14.28%">Miércoles</th><th style="width:14.28%">Jueves</th><th style="width:14.28%">Viernes</th>
                    <th style="width:14.28%">Sábado</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $primerDia = new DateTime($mes . '-01');
                $ultimoDia = new DateTime($primerDia->format('Y-m-t'));
                $inicioSemana = (int) $primerDia->format('w');
                $diasEnMes = (int) $primerDia->format('t');
                $actividadesPorDia = [];
                foreach ($actividades as $a) {
                    $d = date('j', strtotime($a['fecha_hora']));
                    $actividadesPorDia[(int)$d][] = $a;
                }
                $dia = 1;
                $hoy = (int)date('j');
                $mesActual = date('Y-m');
                ?>
                <?php for ($fila = 0; $fila < 6 && $dia <= $diasEnMes; $fila++): ?>
                <tr>
                    <?php for ($col = 0; $col < 7; $col++): ?>
                    <td style="height: 100px; vertical-align: top; <?= ($fila === 0 && $col < $inicioSemana) || $dia > $diasEnMes ? 'background:#f8f9fa;' : '' ?> <?= $dia == $hoy && $mes === $mesActual ? 'background:#cfe2ff;' : '' ?>">
                        <?php if (($fila > 0 || $col >= $inicioSemana) && $dia <= $diasEnMes): ?>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold"><?= $dia ?></span>
                            <button class="btn btn-sm btn-link p-0 text-success" onclick="abrirModalActividad(null, '<?= $mes . '-' . str_pad($dia, 2, '0', STR_PAD_LEFT) ?>')"><i class="bi bi-plus-circle"></i></button>
                        </div>
                        <?php if (!empty($actividadesPorDia[$dia])): ?>
                        <?php foreach ($actividadesPorDia[$dia] as $act): ?>
                        <div class="mb-1 p-1 rounded" style="font-size:0.7rem; background: <?= safe_string($act['color'] ?? '#0d6efd') ?>20; border-left: 3px solid <?= safe_string($act['color'] ?? '#0d6efd') ?>; cursor:pointer;" onclick="abrirModalActividad(<?= $act['id_actividad'] ?>)">
                            <div class="fw-bold"><?= safe_string(truncate($act['titulo'], 20)) ?></div>
                            <small><?= date('H:i', strtotime($act['fecha_hora'])) ?> - <?= safe_string($act['tipo']) ?></small>
                            <?php if ($act['estado'] === 'completada'): ?>
                            <span class="badge bg-success">✓</span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        <?php $dia++; ?>
                        <?php endif; ?>
                    </td>
                    <?php endfor; ?>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Actividad -->
<div class="modal fade" id="actividadModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form id="actividadForm" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="actModalTitle">Nueva Actividad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="id_actividad" id="act_id">
                    <div class="mb-2">
                        <label class="form-label">Título</label>
                        <input type="text" name="titulo" id="act_titulo" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" id="act_descripcion" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">
                            <label class="form-label">Tipo</label>
                            <select name="tipo" id="act_tipo" class="form-select form-select-sm">
                                <option value="tarea">Tarea</option><option value="cita">Cita</option>
                                <option value="llamada">Llamada</option><option value="recordatorio">Recordatorio</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Color</label>
                            <input type="color" name="color" id="act_color" class="form-control form-control-sm" value="#0d6efd">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Fecha y hora</label>
                        <input type="datetime-local" name="fecha_hora" id="act_fecha" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <div class="form-check">
                            <input type="checkbox" name="recordatorio" id="act_recordatorio" class="form-check-input" value="1">
                            <label class="form-check-label">Enviar recordatorio</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-danger" onclick="eliminarActividad()" id="btnEliminarAct" style="display:none"><i class="bi bi-trash"></i></button>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-check-lg"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function abrirModalActividad(id, fecha) {
    document.getElementById('actModalTitle').textContent = id ? 'Editar Actividad' : 'Nueva Actividad';
    document.getElementById('act_id').value = id || '';
    document.getElementById('btnEliminarAct').style.display = id ? 'inline-block' : 'none';
    if (!id) {
        document.getElementById('act_titulo').value = '';
        document.getElementById('act_descripcion').value = '';
        document.getElementById('act_tipo').value = 'tarea';
        document.getElementById('act_color').value = '#0d6efd';
        document.getElementById('act_fecha').value = fecha ? fecha + 'T09:00' : new Date().toISOString().slice(0,16);
        document.getElementById('act_recordatorio').checked = false;
        document.getElementById('actividadForm').action = '<?= url('actividades/store') ?>';
    } else {
        fetch('<?= url('agenda/data?mes=') ?>' + '<?= $mes ?>')
            .then(r => r.json())
            .then(data => {
                const act = data.find(a => a.id_actividad == id);
                if (!act) return;
                document.getElementById('act_titulo').value = act.titulo;
                document.getElementById('act_descripcion').value = act.descripcion || '';
                document.getElementById('act_tipo').value = act.tipo;
                document.getElementById('act_color').value = act.color || '#0d6efd';
                document.getElementById('act_fecha').value = act.fecha_hora.replace(' ', 'T').slice(0,16);
                document.getElementById('act_recordatorio').checked = act.recordatorio == 1;
                document.getElementById('actividadForm').action = '<?= url('actividades/update') ?>/' + id;
            });
    }
    new bootstrap.Modal(document.getElementById('actividadModal')).show();
}

function eliminarActividad() {
    if (!confirm('¿Eliminar actividad?')) return;
    const id = document.getElementById('act_id').value;
    const formData = new FormData();
    formData.append('csrf_token', '<?= csrf_token() ?>');
    fetch('<?= url('actividades/delete') ?>/' + id, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(d => { if (d.success) location.reload(); });
}
</script>
