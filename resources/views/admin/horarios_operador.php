<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h4 mb-0"><i class="bi bi-clock"></i> Horarios de Operadores</h1>
    <span class="badge bg-info"><?= safe_string($rol_nombre) ?></span>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-dark text-white py-1 px-2"><small><i class="bi bi-people"></i> Asignación de Turnos y Accesos</small></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th>Operador</th>
                        <th>Turno</th>
                        <th>Horario</th>
                        <th>Activo</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($operadores as $op): ?>
                    <tr>
                        <td><?= safe_string($op['nombre'] . ' ' . $op['apellido_paterno']) ?></td>
                        <td>
                            <span class="badge bg-<?= ($op['turno'] ?? '') === 'matutino' ? 'warning' : (($op['turno'] ?? '') === 'vespertino' ? 'info' : 'dark') ?> text-dark">
                                <?= $op['turno'] ? ucfirst($op['turno']) : '<span class="text-muted">Sin asignar</span>' ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($op['hora_inicio']): ?>
                                <?= substr($op['hora_inicio'], 0, 5) ?> — <?= substr($op['hora_fin'], 0, 5) ?>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($op['activo']): ?>
                            <span class="badge bg-success">Sí</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">No</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-1"
                                data-bs-toggle="modal" data-bs-target="#editarHorarioModal"
                                data-id-empleado="<?= $op['id_empleado'] ?>"
                                data-nombre="<?= safe_string($op['nombre'] . ' ' . $op['apellido_paterno']) ?>"
                                data-turno="<?= $op['turno'] ?? 'matutino' ?>"
                                data-hora-inicio="<?= $op['hora_inicio'] ?? '06:00:00' ?>"
                                data-hora-fin="<?= $op['hora_fin'] ?? '14:00:00' ?>"
                                data-activo="<?= $op['activo'] ?? 1 ?>"
                                title="Editar horario"><i class="bi bi-pencil"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-warning py-0 px-1"
                                data-bs-toggle="modal" data-bs-target="#autorizarAccesoModal"
                                data-id-empleado="<?= $op['id_empleado'] ?>"
                                data-nombre="<?= safe_string($op['nombre'] . ' ' . $op['apellido_paterno']) ?>"
                                title="Autorizar acceso fuera de horario"><i class="bi bi-key"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Editar Horario Modal -->
<div class="modal fade" id="editarHorarioModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form method="POST" action="<?= url('admin/horarios/guardar') ?>">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="id_empleado" id="edit_id_empleado">
                <div class="modal-header bg-dark text-white py-1">
                    <h5 class="modal-title"><i class="bi bi-clock"></i> Editar Horario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2" id="edit_nombre" style="font-weight:600;"></p>
                    <div class="mb-2">
                        <label class="form-label small">Turno</label>
                        <select name="turno" class="form-select form-select-sm" id="edit_turno">
                            <option value="matutino">Matutino</option>
                            <option value="vespertino">Vespertino</option>
                            <option value="nocturno">Nocturno</option>
                        </select>
                    </div>
                    <div class="row g-1 mb-2">
                        <div class="col-6">
                            <label class="form-label small">Inicio</label>
                            <input type="time" name="hora_inicio" class="form-control form-control-sm" id="edit_hora_inicio" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Fin</label>
                            <input type="time" name="hora_fin" class="form-control form-control-sm" id="edit_hora_fin" required>
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="activo" class="form-check-input" id="edit_activo" value="1" checked>
                        <label class="form-check-label small" for="edit_activo">Horario activo</label>
                    </div>
                </div>
                <div class="modal-footer p-1">
                    <button type="submit" class="btn btn-dark btn-sm w-100"><i class="bi bi-save"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Autorizar Acceso Modal -->
<div class="modal fade" id="autorizarAccesoModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form method="POST" action="<?= url('admin/horarios/autorizar') ?>">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="id_empleado" id="aut_id_empleado">
                <div class="modal-header bg-warning text-dark py-1">
                    <h5 class="modal-title"><i class="bi bi-key"></i> Autorizar Acceso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2" id="aut_nombre" style="font-weight:600;"></p>
                    <p class="small text-muted mb-2">Autoriza a este operador a acceder fuera de su horario:</p>
                    <div class="mb-2">
                        <label class="form-label small">Duración (horas)</label>
                        <input type="number" name="horas" class="form-control form-control-sm" value="1" min="1" max="24">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Motivo</label>
                        <input type="text" name="motivo" class="form-control form-control-sm" placeholder="Cobertura, tiempo extra, etc.">
                    </div>
                </div>
                <div class="modal-footer p-1">
                    <button type="submit" class="btn btn-warning btn-sm w-100"><i class="bi bi-check-lg"></i> Autorizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var editModal = document.getElementById('editarHorarioModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            var btn = event.relatedTarget;
            document.getElementById('edit_id_empleado').value = btn.getAttribute('data-id-empleado');
            document.getElementById('edit_nombre').textContent = btn.getAttribute('data-nombre');
            document.getElementById('edit_turno').value = btn.getAttribute('data-turno');
            document.getElementById('edit_hora_inicio').value = btn.getAttribute('data-hora-inicio').substring(0, 5);
            document.getElementById('edit_hora_fin').value = btn.getAttribute('data-hora-fin').substring(0, 5);
            document.getElementById('edit_activo').checked = btn.getAttribute('data-activo') === '1';
        });
    }
    var autModal = document.getElementById('autorizarAccesoModal');
    if (autModal) {
        autModal.addEventListener('show.bs.modal', function(event) {
            var btn = event.relatedTarget;
            document.getElementById('aut_id_empleado').value = btn.getAttribute('data-id-empleado');
            document.getElementById('aut_nombre').textContent = btn.getAttribute('data-nombre');
        });
    }
});
</script>
