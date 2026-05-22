<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-graph-up"></i> KPIs e Indicadores</h1>
</div>

<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white"><i class="bi bi-speedometer2"></i> Indicadores Clave</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Indicador</th><th>Valor</th><th>Unidad</th><th>Objetivo</th><th>Estatus</th><th>Fecha</th></tr></thead>
                        <tbody>
                            <?php foreach ($kpis as $k): ?>
                            <tr>
                                <td><?= safe_string($k['indicador']) ?></td>
                                <td><?= number_format($k['valor'], 2) ?></td>
                                <td><?= safe_string($k['unidad']) ?></td>
                                <td><?= number_format($k['objetivo'], 2) ?></td>
                                <td><span class="badge bg-<?= $k['valor'] >= $k['objetivo'] ? 'success' : 'danger' ?>"><?= $k['valor'] >= $k['objetivo'] ? 'Cumplido' : 'Debajo' ?></span></td>
                                <td><?= format_date($k['fecha_calculo']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($kpis)): ?>
                            <tr><td colspan="6" class="text-center text-muted">Sin KPIs</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white"><i class="bi bi-bar-chart-line"></i> OEE Global</div>
            <div class="card-body">
                <canvas id="oeeChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white"><i class="bi bi-cpu"></i> OEE por Máquina</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Máquina</th><th>Fecha</th><th>Disp.</th><th>Rend.</th><th>Cal.</th><th>OEE</th></tr></thead>
                        <tbody>
                            <?php foreach ($oee as $o): ?>
                            <tr>
                                <td><?= safe_string($o['maquina_nombre'] ?? 'N/A') ?></td>
                                <td><?= format_date($o['fecha']) ?></td>
                                <td><?= number_format($o['disponibilidad_percent'], 1) ?>%</td>
                                <td><?= number_format($o['rendimiento_percent'], 1) ?>%</td>
                                <td><?= number_format($o['calidad_percent'], 1) ?>%</td>
                                <td><strong><?= number_format($o['oee_percent'], 1) ?>%</strong></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($oee)): ?>
                            <tr><td colspan="6" class="text-center text-muted">Sin datos OEE</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white"><i class="bi bi-bar-chart"></i> Eficiencia Operativa</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Máquina</th><th>Fecha</th><th>Disp.</th><th>Rend.</th><th>Cal.</th><th>OEE</th></tr></thead>
                        <tbody>
                            <?php foreach ($eficiencia as $e): ?>
                            <tr>
                                <td><?= safe_string($e['maquina_nombre'] ?? 'N/A') ?></td>
                                <td><?= format_date($e['fecha']) ?></td>
                                <td><?= number_format($e['disponibilidad_percent'], 1) ?>%</td>
                                <td><?= number_format($e['rendimiento_percent'], 1) ?>%</td>
                                <td><?= number_format($e['calidad_percent'], 1) ?>%</td>
                                <td><strong><?= number_format($e['oee_percent'], 1) ?>%</strong></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($eficiencia)): ?>
                            <tr><td colspan="6" class="text-center text-muted">Sin datos</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const oeeData = <?= json_encode(array_map(function($o) {
        return ['maquina' => $o['maquina_nombre'] ?? 'N/A', 'oee' => (float)($o['oee_percent'] ?? 0)];
    }, array_slice($oee, 0, 10))) ?>;

    if (oeeData.length > 0 && document.getElementById('oeeChart')) {
        new Chart(document.getElementById('oeeChart'), {
            type: 'bar',
            data: {
                labels: oeeData.map(d => d.maquina),
                datasets: [{
                    label: 'OEE %',
                    data: oeeData.map(d => d.oee),
                    backgroundColor: oeeData.map(d => d.oee >= 85 ? '#198754' : d.oee >= 70 ? '#ffc107' : '#dc3545'),
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, max: 100 } }
            }
        });
    }
});
</script>
