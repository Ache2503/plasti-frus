<?php if (isset($exportUrl) || isset($exportPdfUrl) || isset($exportExcelUrl)): ?>
<div class="btn-group btn-group-sm mb-2">
    <?php if (isset($exportPdfUrl)): ?>
    <a href="<?= url($exportPdfUrl) ?>" class="btn btn-outline-danger" target="_blank" title="Exportar PDF">
        <i class="bi bi-file-earmark-pdf"></i> PDF
    </a>
    <?php endif; ?>
    <?php if (isset($exportExcelUrl)): ?>
    <a href="<?= url($exportExcelUrl) ?>" class="btn btn-outline-success" title="Exportar Excel">
        <i class="bi bi-file-earmark-excel"></i> Excel
    </a>
    <?php endif; ?>
    <?php if (isset($exportUrl)): ?>
    <a href="<?= url($exportUrl) ?>" class="btn btn-outline-secondary" title="Exportar">
        <i class="bi bi-download"></i> Exportar
    </a>
    <?php endif; ?>
</div>
<?php endif; ?>
