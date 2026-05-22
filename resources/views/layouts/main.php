<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= safe_string($pageTitle ?? APP_NAME) ?> — <?= APP_NAME ?></title>
    <link rel="icon" href="<?= asset('images/logo.png') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="<?= asset('css/main.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/dashboard.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/enhanced.css') ?>" rel="stylesheet">
</head>
<body>
    <?php if (isset($_SESSION['user_id'])): ?>
        <?php
        if (($GLOBALS['_SERVER']['REQUEST_URI'] ?? '') !== '/acceso-denegado'
            && ($_SESSION['user_rol'] ?? null) === 2
            && !\verificar_acceso_operador()['permitido']
        ) {
            session_destroy();
            header('Location: /acceso-denegado');
            exit;
        }
        ?>
        <?php require __DIR__ . '/header.php'; ?>
        <div class="container-fluid">
            <div class="row">
                <?php require __DIR__ . '/sidebar.php'; ?>
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    <?php if (isset($breadcrumbs)): ?>
                    <nav class="breadcrumb-custom pt-3 pb-0" aria-label="breadcrumb">
                        <?php foreach ($breadcrumbs as $b): ?>
                            <?php if (isset($b['url'])): ?>
                                <a href="<?= url($b['url']) ?>"><?= safe_string($b['label']) ?></a>
                            <?php else: ?>
                                <span class="text-dark fw-semibold"><?= safe_string($b['label']) ?></span>
                            <?php endif; ?>
                            <?php if ($b !== end($breadcrumbs)): ?>
                                <span class="separator">›</span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </nav>
                    <?php endif; ?>
                    <div class="py-3 page-transition">
                        <?php require $content; ?>
                    </div>
                </main>
            </div>
        </div>
        <?php require __DIR__ . '/footer.php'; ?>
    <?php else: ?>
        <div class="container">
            <?php require $content; ?>
        </div>
    <?php endif; ?>

    <?php if ($flash = flash_message()): ?>
    <script id="flashData" type="application/json"><?= json_encode(['type' => $flash['type'], 'message' => $flash['message']], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="<?= asset('js/main.js') ?>"></script>
    <script src="<?= asset('js/charts.js') ?>"></script>
    <script src="<?= asset('js/validations.js') ?>"></script>
</body>
</html>
