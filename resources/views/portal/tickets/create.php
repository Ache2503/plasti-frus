<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-plus-circle"></i> Nuevo Ticket de Soporte</h1>
    <a href="<?= url('tickets') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Mis Tickets</a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="<?= url('tickets/guardar') ?>">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
                        <input type="text" name="titulo" class="form-control" placeholder="Ej: Problema con mi pedido #123" value="<?= safe_string($_SESSION['_old']['titulo'] ?? '') ?>" required maxlength="255">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Prioridad</label>
                        <select name="prioridad" class="form-select">
                            <option value="baja">Baja</option>
                            <option value="media" selected>Media</option>
                            <option value="alta">Alta</option>
                            <option value="urgente">Urgente</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Descripción <span class="text-danger">*</span></label>
                        <textarea name="descripcion" class="form-control" rows="6" placeholder="Describe tu problema o consulta con el mayor detalle posible..." required><?= safe_string($_SESSION['_old']['descripcion'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= url('tickets') ?>" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-dark"><i class="bi bi-send"></i> Enviar Ticket</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php unset($_SESSION['_old']); ?>
