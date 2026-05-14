<div class="d-flex justify-content-center pt-4">
    <div class="card shadow-sm" style="max-width: 500px; width: 100%;">
        <div class="card-body text-center p-4">
            <div class="mb-4">
                <i class="bi bi-receipt" style="font-size: 3rem; color: var(--bs-primary);"></i>
            </div>
            <h4 class="mb-1">Facturación Electrónica</h4>
            <p class="text-muted small mb-4">Ingresa el código único que aparece en tu ticket para solicitar tu factura.</p>

            <form method="POST" action="<?= url('factura/buscar') ?>">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Código de facturación</label>
                    <input type="text" name="folio_unico" class="form-control form-control-lg text-center" placeholder="Ej: TKT-20260513-A1B2C3" required autofocus>
                </div>
                <button type="submit" class="btn btn-dark btn-lg w-100"><i class="bi bi-search"></i> Buscar</button>
            </form>

            <hr class="my-4">
            <p class="text-muted small mb-0">
                <i class="bi bi-info-circle"></i>
                El código se encuentra en la parte inferior de tu ticket de compra.
            </p>

        </div>
    </div>
</div>
