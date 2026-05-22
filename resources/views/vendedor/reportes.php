<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h4 mb-0"><i class="bi bi-file-earmark-bar-graph"></i> Reportes Comerciales</h1>
</div>

<div class="row g-2 mb-3">
    <div class="col-auto">
        <div class="btn-group" role="group">
            <button class="btn btn-sm <?= ($tipo_reporte ?? 'productos') === 'productos' ? 'btn-dark' : 'btn-outline-dark' ?>" onclick="cargarReporte('productos')">Por Producto</button>
            <button class="btn btn-sm <?= ($tipo_reporte ?? '') === 'clientes' ? 'btn-dark' : 'btn-outline-dark' ?>" onclick="cargarReporte('clientes')">Por Cliente</button>
            <button class="btn btn-sm <?= ($tipo_reporte ?? '') === 'pipeline' ? 'btn-dark' : 'btn-outline-dark' ?>" onclick="cargarReporte('pipeline')">Pipeline</button>
            <button class="btn btn-sm <?= ($tipo_reporte ?? '') === 'comisiones' ? 'btn-dark' : 'btn-outline-dark' ?>" onclick="cargarReporte('comisiones')">Comisiones</button>
        </div>
    </div>
</div>

<form method="GET" class="row g-2 mb-3 align-items-end">
    <input type="hidden" name="tipo" id="tipo_reporte" value="<?= safe_string($tipo_reporte ?? 'productos') ?>">
    <div class="col-auto">
        <label class="form-label">Desde</label>
        <input type="date" name="desde" class="form-control form-control-sm" value="<?= safe_string($desde ?? date('Y-m-d', strtotime('-1 year'))) ?>">
    </div>
    <div class="col-auto">
        <label class="form-label">Hasta</label>
        <input type="date" name="hasta" class="form-control form-control-sm" value="<?= safe_string($hasta ?? date('Y-m-d')) ?>">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-funnel"></i> Filtrar</button>
        <button type="button" class="btn btn-sm btn-success" onclick="exportarCSV()"><i class="bi bi-download"></i> Exportar CSV</button>
        <button type="button" class="btn btn-sm btn-danger" onclick="exportarPDF()"><i class="bi bi-filetype-pdf"></i> Exportar PDF</button>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0" id="reporteTable">
                <thead>
                    <tr>
                        <?php if ($tipo_reporte === 'productos'): ?>
                        <th>Producto</th><th class="text-end">Cantidad</th><th class="text-end">Total</th>
                        <?php elseif ($tipo_reporte === 'clientes'): ?>
                        <th>Cliente</th><th class="text-end">Ventas</th><th class="text-end">Total</th>
                        <?php elseif ($tipo_reporte === 'pipeline'): ?>
                        <th>Etapa</th><th class="text-end">Oportunidades</th><th class="text-end">Valor Total</th>
                        <?php elseif ($tipo_reporte === 'comisiones'): ?>
                        <th>Mes</th><th class="text-end">Comisión</th><th>Estatus</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($tipo_reporte === 'productos'): ?>
                    <?php foreach ($datos as $d): ?>
                    <tr><td><?= safe_string($d['nombre'] ?? 'N/A') ?></td><td class="text-end"><?= $d['cantidad'] ?? 0 ?></td><td class="text-end"><?= format_money($d['total'] ?? 0) ?></td></tr>
                    <?php endforeach; ?>
                    <?php elseif ($tipo_reporte === 'clientes'): ?>
                    <?php foreach ($datos as $d): ?>
                    <tr><td><?= safe_string($d['razon_social'] ?? 'N/A') ?></td><td class="text-end"><?= $d['total_ventas'] ?? 0 ?></td><td class="text-end"><?= format_money($d['total'] ?? 0) ?></td></tr>
                    <?php endforeach; ?>
                    <?php elseif ($tipo_reporte === 'pipeline'): ?>
                    <?php foreach ($datos as $d): ?>
                    <tr><td><?= safe_string($etapasLabels[$d['etapa']] ?? $d['etapa']) ?></td><td class="text-end"><?= $d['total'] ?? 0 ?></td><td class="text-end"><?= format_money($d['valor_total'] ?? 0) ?></td></tr>
                    <?php endforeach; ?>
                    <?php elseif ($tipo_reporte === 'comisiones'): ?>
                    <?php foreach ($datos as $d): ?>
                    <tr>
                        <td><?= format_date($d['fecha_calculo'] ?? $d['created_at']) ?></td>
                        <td class="text-end"><?= format_money($d['monto_comision'] ?? 0) ?></td>
                        <td><span class="badge bg-<?= $d['estatus'] === 'pagada' ? 'success' : 'warning' ?>"><?= safe_string($d['estatus'] ?? 'pendiente') ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (empty($datos)): ?>
                    <tr><td colspan="3" class="text-center text-muted">Sin datos</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function cargarReporte(tipo) {
    document.getElementById('tipo_reporte').value = tipo;
    const form = document.querySelector('form');
    form.submit();
}

function exportarCSV() {
    const rows = [];
    document.querySelectorAll('#reporteTable tbody tr').forEach(tr => {
        const cols = [];
        tr.querySelectorAll('td').forEach(td => cols.push(td.textContent.trim()));
        if (cols.length > 0) rows.push(cols.join(','));
    });
    const header = [];
    document.querySelectorAll('#reporteTable thead th').forEach(th => header.push(th.textContent.trim()));
    const csv = [header.join(','), ...rows].join('\n');
    const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'reporte_<?= safe_string($tipo_reporte ?? 'export') ?>.csv';
    link.click();
}

function exportarPDF() {
    window.open('<?= url('reportes-vendedor/export') ?>?tipo=<?= safe_string($tipo_reporte ?? 'productos') ?>&desde=<?= safe_string($desde ?? '') ?>&hasta=<?= safe_string($hasta ?? '') ?>', '_blank');
}
</script>
