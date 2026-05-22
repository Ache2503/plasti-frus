<?php if (!empty($paginator) && $paginator['totalPages'] > 1): ?>
<nav aria-label="Paginación">
    <ul class="pagination justify-content-center">
        <?php if ($paginator['page'] > 1): ?>
        <li class="page-item">
            <a class="page-link" href="<?= url(strtok($_SERVER['REQUEST_URI'], '?') . '?page=' . ($paginator['page'] - 1)) ?>">&laquo; Anterior</a>
        </li>
        <?php endif; ?>
        
        <?php 
        $start = max(1, $paginator['page'] - 2);
        $end = min($paginator['totalPages'], $paginator['page'] + 2);
        for ($i = $start; $i <= $end; $i++): 
        ?>
        <li class="page-item <?= $i === $paginator['page'] ? 'active' : '' ?>">
            <a class="page-link" href="<?= url(strtok($_SERVER['REQUEST_URI'], '?') . '?page=' . $i) ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
        
        <?php if ($paginator['page'] < $paginator['totalPages']): ?>
        <li class="page-item">
            <a class="page-link" href="<?= url(strtok($_SERVER['REQUEST_URI'], '?') . '?page=' . ($paginator['page'] + 1)) ?>">Siguiente &raquo;</a>
        </li>
        <?php endif; ?>
    </ul>
    <p class="text-center text-muted small">
        Mostrando página <?= $paginator['page'] ?> de <?= $paginator['totalPages'] ?> 
        (<?= $paginator['total'] ?> registros)
    </p>
</nav>
<?php endif; ?>
