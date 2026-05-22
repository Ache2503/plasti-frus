<?php if (isset($pagination) && $pagination instanceof \App\Core\Pagination): ?>
<div class="d-flex justify-content-center flex-column align-items-center mt-3">
    <?= $pagination->render() ?>
</div>
<?php endif; ?>
