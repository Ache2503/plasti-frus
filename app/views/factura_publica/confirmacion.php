<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm text-center">
            <div class="card-body p-5">
                <div class="mb-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                </div>
                <h3 class="mb-2">¡Solicitud Enviada!</h3>
                <p class="text-muted mb-3">
                    Tu solicitud de factura para el ticket <strong><?= safe_string($folio) ?></strong>
                    ha sido registrada correctamente.
                </p>
                <div class="alert alert-info small">
                    <i class="bi bi-info-circle"></i>
                    Recibirás tu factura en los próximos días hábiles una vez que sea procesada.
                </div>
                <a href="<?= url('factura') ?>" class="btn btn-outline-dark"><i class="bi bi-arrow-left"></i> Nueva consulta</a>
            </div>
        </div>
    </div>
</div>
