<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-plus-lg"></i> Nueva Póliza</h1>
    <a href="<?= url('contabilidad/polizas') ?>" class="btn btn-sm btn-outline-dark"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="post" action="<?= url('contabilidad/polizas/store') ?>" id="formPoliza">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tipo <span class="text-danger">*</span></label>
                    <select name="tipo" class="form-select" required>
                        <option value="diario">Diario</option>
                        <option value="ingreso">Ingreso</option>
                        <option value="egreso">Egreso</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Fecha <span class="text-danger">*</span></label>
                    <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Concepto <span class="text-danger">*</span></label>
                <textarea name="concepto" class="form-control" rows="2" required></textarea>
            </div>

            <hr>
            <h5><i class="bi bi-list-columns"></i> Partidas</h5>
            <div class="table-responsive">
                <table class="table table-sm table-bordered" id="tablaPartidas">
                    <thead>
                        <tr>
                            <th style="width:35%">Cuenta</th>
                            <th>Concepto</th>
                            <th style="width:15%">Cargo</th>
                            <th style="width:15%">Abono</th>
                            <th style="width:5%"></th>
                        </tr>
                    </thead>
                    <tbody id="partidasBody">
                        <tr>
                            <td>
                                <select name="id_cuenta[]" class="form-select form-select-sm" required>
                                    <option value="">-- Seleccionar --</option>
                                    <?php foreach ($cuentas as $c): ?>
                                    <option value="<?= $c['id_cuenta'] ?>"><?= safe_string($c['codigo'] . ' - ' . $c['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="text" name="concepto_partida[]" class="form-control form-control-sm"></td>
                            <td><input type="number" name="cargo[]" class="form-control form-control-sm cargo" step="0.01" min="0" value="0"></td>
                            <td><input type="number" name="abono[]" class="form-control form-control-sm abono" step="0.01" min="0" value="0"></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger eliminar-partida"><i class="bi bi-x"></i></button></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" class="text-end">Totales:</th>
                            <th class="text-end" id="totalCargo">$0.00</th>
                            <th class="text-end" id="totalAbono">$0.00</th>
                            <th></th>
                        </tr>
                        <tr>
                            <th colspan="2" class="text-end">Diferencia:</th>
                            <th colspan="2" class="text-end" id="diferencia">$0.00</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="agregarPartida"><i class="bi bi-plus-lg"></i> Agregar Partida</button>
            <br>
            <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Guardar Póliza</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function recalcular() {
        let totalCargo = 0, totalAbono = 0;
        document.querySelectorAll('.cargo').forEach(i => totalCargo += parseFloat(i.value) || 0);
        document.querySelectorAll('.abono').forEach(i => totalAbono += parseFloat(i.value) || 0);
        document.getElementById('totalCargo').textContent = '$' + totalCargo.toFixed(2);
        document.getElementById('totalAbono').textContent = '$' + totalAbono.toFixed(2);
        const diff = totalCargo - totalAbono;
        const diffEl = document.getElementById('diferencia');
        diffEl.textContent = '$' + Math.abs(diff).toFixed(2);
        diffEl.className = Math.abs(diff) < 0.01 ? 'text-success' : 'text-danger';
    }

    document.getElementById('agregarPartida').addEventListener('click', function() {
        const tbody = document.getElementById('partidasBody');
        const row = tbody.querySelector('tr').cloneNode(true);
        row.querySelectorAll('input').forEach(i => i.value = '');
        row.querySelector('select').value = '';
        tbody.appendChild(row);
        row.querySelectorAll('input').forEach(i => i.addEventListener('input', recalcular));
        row.querySelector('.eliminar-partida').addEventListener('click', function() {
            if (tbody.children.length > 1) {
                row.remove();
                recalcular();
            }
        });
    });

    document.querySelectorAll('.eliminar-partida').forEach(btn => {
        btn.addEventListener('click', function() {
            const tbody = document.getElementById('partidasBody');
            if (tbody.children.length > 1) {
                this.closest('tr').remove();
                recalcular();
            }
        });
    });

    document.querySelectorAll('.cargo, .abono').forEach(i => i.addEventListener('input', recalcular));
});
</script>
