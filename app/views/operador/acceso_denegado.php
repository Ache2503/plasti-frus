<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Denegado — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .denied-card { max-width: 480px; border: none; border-top: 4px solid #dc3545; box-shadow: 0 4px 24px rgba(0,0,0,0.1); }
        .denied-icon { font-size: 4rem; color: #dc3545; }
    </style>
</head>
<body>
    <div class="card denied-card p-4 text-center">
        <div class="denied-icon mb-3"><i class="bi bi-shield-lock-fill"></i></div>
        <h3 class="fw-bold text-danger">Acceso Denegado</h3>
        <p class="text-muted mb-3">
            <?= safe_string($_SESSION['error_acceso'] ?? 'No puedes acceder al sistema en este momento por políticas de la empresa.') ?>
        </p>
        <hr>
        <p class="small text-muted mb-3">
            Si estás cubriendo a otro operador, solicita a tu supervisor que autorice tu acceso.
        </p>
        <div class="d-grid gap-2">
            <a href="<?= url('login') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver al inicio</a>
        </div>
    </div>
</body>
</html>
