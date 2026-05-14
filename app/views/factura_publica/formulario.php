<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">
                <i class="bi bi-receipt"></i> Datos de la Venta
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><th class="w-25">Folio ticket</th><td><strong><?= safe_string($ticket['folio_unico']) ?></strong></td></tr>
                    <tr><th>Producto</th><td><?= safe_string($ticket['producto_nombre'] ?? 'N/A') ?> (<?= safe_string($ticket['producto_codigo'] ?? '') ?>)</td></tr>
                    <tr><th>Cantidad</th><td><?= $ticket['cantidad_vendida'] ?></td></tr>
                    <tr><th>Precio unitario</th><td><?= format_money($ticket['precio_unitario'], $ticket['moneda']) ?></td></tr>
                    <tr><th>Total</th><td><strong><?= format_money($ticket['cantidad_vendida'] * $ticket['precio_unitario'], $ticket['moneda']) ?></strong></td></tr>
                    <tr><th>Fecha venta</th><td><?= format_date($ticket['fecha_venta']) ?></td></tr>
                </table>
                <div class="mt-3">
                    <a href="<?= url('factura/pdf/' . $ticket['folio_unico']) ?>" class="btn btn-sm btn-outline-dark" target="_blank"><i class="bi bi-download"></i> Descargar PDF</a>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <i class="bi bi-person-lines-fill"></i> Datos para Facturación
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('factura/solicitar/' . $ticket['folio_unico']) ?>">
                    <div class="mb-3">
                        <label class="form-label">Razón Social <span class="text-danger">*</span></label>
                        <input type="text" name="razon_social" class="form-control" value="<?= safe_string(old('razon_social', $ticket['razon_social'] ?? '')) ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">RFC <span class="text-danger">*</span></label>
                            <input type="text" name="rfc" class="form-control" value="<?= safe_string(old('rfc', $ticket['rfc'] ?? '')) ?>" placeholder="XXXX000000XXX" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Código Postal <span class="text-danger">*</span></label>
                            <input type="text" name="codigo_postal" class="form-control" value="<?= safe_string(old('codigo_postal', $ticket['codigo_postal'] ?? '')) ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Régimen Fiscal <span class="text-danger">*</span></label>
                            <select name="regimen_fiscal" class="form-select" required>
                                <option value="">— Seleccionar —</option>
                                <option value="601" <?= old('regimen_fiscal', $ticket['regimen_fiscal'] ?? '') === '601' ? 'selected' : '' ?>>601 - General de Ley Personas Morales</option>
                                <option value="603" <?= old('regimen_fiscal', $ticket['regimen_fiscal'] ?? '') === '603' ? 'selected' : '' ?>>603 - Personas Morales con Fines no Lucrativos</option>
                                <option value="605" <?= old('regimen_fiscal', $ticket['regimen_fiscal'] ?? '') === '605' ? 'selected' : '' ?>>605 - Sueldos y Salarios e Ingresos Asimilados</option>
                                <option value="606" <?= old('regimen_fiscal', $ticket['regimen_fiscal'] ?? '') === '606' ? 'selected' : '' ?>>606 - Arrendamiento</option>
                                <option value="607" <?= old('regimen_fiscal', $ticket['regimen_fiscal'] ?? '') === '607' ? 'selected' : '' ?>>607 - Régimen de Enajenación o Adquisición de Bienes</option>
                                <option value="608" <?= old('regimen_fiscal', $ticket['regimen_fiscal'] ?? '') === '608' ? 'selected' : '' ?>>608 - Demás ingresos</option>
                                <option value="609" <?= old('regimen_fiscal', $ticket['regimen_fiscal'] ?? '') === '609' ? 'selected' : '' ?>>609 - Consolidación</option>
                                <option value="610" <?= old('regimen_fiscal', $ticket['regimen_fiscal'] ?? '') === '610' ? 'selected' : '' ?>>610 - Residentes en el Extranjero sin Establecimiento Permanente</option>
                                <option value="611" <?= old('regimen_fiscal', $ticket['regimen_fiscal'] ?? '') === '611' ? 'selected' : '' ?>>611 - Ingresos por Dividendos (socios y accionistas)</option>
                                <option value="612" <?= old('regimen_fiscal', $ticket['regimen_fiscal'] ?? '') === '612' ? 'selected' : '' ?>>612 - Personas Físicas con Actividades Empresariales y Profesionales</option>
                                <option value="614" <?= old('regimen_fiscal', $ticket['regimen_fiscal'] ?? '') === '614' ? 'selected' : '' ?>>614 - Ingresos por intereses</option>
                                <option value="615" <?= old('regimen_fiscal', $ticket['regimen_fiscal'] ?? '') === '615' ? 'selected' : '' ?>>615 - Régimen de los ingresos por obtención de premios</option>
                                <option value="616" <?= old('regimen_fiscal', $ticket['regimen_fiscal'] ?? '') === '616' ? 'selected' : '' ?>>616 - Sin obligaciones fiscales</option>
                                <option value="620" <?= old('regimen_fiscal', $ticket['regimen_fiscal'] ?? '') === '620' ? 'selected' : '' ?>>620 - Sociedades Cooperativas de Producción</option>
                                <option value="621" <?= old('regimen_fiscal', $ticket['regimen_fiscal'] ?? '') === '621' ? 'selected' : '' ?>>621 - Régimen de Incorporación Fiscal</option>
                                <option value="622" <?= old('regimen_fiscal', $ticket['regimen_fiscal'] ?? '') === '622' ? 'selected' : '' ?>>622 - Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras</option>
                                <option value="623" <?= old('regimen_fiscal', $ticket['regimen_fiscal'] ?? '') === '623' ? 'selected' : '' ?>>623 - Opcional para Grupos de Sociedades</option>
                                <option value="624" <?= old('regimen_fiscal', $ticket['regimen_fiscal'] ?? '') === '624' ? 'selected' : '' ?>>624 - Coordinados</option>
                                <option value="625" <?= old('regimen_fiscal', $ticket['regimen_fiscal'] ?? '') === '625' ? 'selected' : '' ?>>625 - Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas</option>
                                <option value="626" <?= old('regimen_fiscal', $ticket['regimen_fiscal'] ?? '') === '626' ? 'selected' : '' ?>>626 - Régimen Simplificado de Confianza</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Uso de CFDI <span class="text-danger">*</span></label>
                            <select name="uso_cfdi" class="form-select" required>
                                <option value="">— Seleccionar —</option>
                                <option value="G01" <?= old('uso_cfdi', $ticket['uso_cfdi'] ?? '') === 'G01' ? 'selected' : '' ?>>G01 - Adquisición de mercancías</option>
                                <option value="G02" <?= old('uso_cfdi', $ticket['uso_cfdi'] ?? '') === 'G02' ? 'selected' : '' ?>>G02 - Devoluciones, descuentos o bonificaciones</option>
                                <option value="G03" <?= old('uso_cfdi', $ticket['uso_cfdi'] ?? '') === 'G03' ? 'selected' : '' ?>>G03 - Gastos en general</option>
                                <option value="I01" <?= old('uso_cfdi', $ticket['uso_cfdi'] ?? '') === 'I01' ? 'selected' : '' ?>>I01 - Construcciones</option>
                                <option value="I02" <?= old('uso_cfdi', $ticket['uso_cfdi'] ?? '') === 'I02' ? 'selected' : '' ?>>I02 - Mobiliario y equipo de oficina por inversiones</option>
                                <option value="I03" <?= old('uso_cfdi', $ticket['uso_cfdi'] ?? '') === 'I03' ? 'selected' : '' ?>>I03 - Equipo de transporte</option>
                                <option value="I04" <?= old('uso_cfdi', $ticket['uso_cfdi'] ?? '') === 'I04' ? 'selected' : '' ?>>I04 - Equipo de cómputo y accesorios</option>
                                <option value="I05" <?= old('uso_cfdi', $ticket['uso_cfdi'] ?? '') === 'I05' ? 'selected' : '' ?>>I05 - Dados, troqueles, moldes, matrices y herramental</option>
                                <option value="I06" <?= old('uso_cfdi', $ticket['uso_cfdi'] ?? '') === 'I06' ? 'selected' : '' ?>>I06 - Comunicaciones telefónicas</option>
                                <option value="I07" <?= old('uso_cfdi', $ticket['uso_cfdi'] ?? '') === 'I07' ? 'selected' : '' ?>>I07 - Comunicaciones satelitales</option>
                                <option value="I08" <?= old('uso_cfdi', $ticket['uso_cfdi'] ?? '') === 'I08' ? 'selected' : '' ?>>I08 - Otra maquinaria y equipo</option>
                                <option value="D01" <?= old('uso_cfdi', $ticket['uso_cfdi'] ?? '') === 'D01' ? 'selected' : '' ?>>D01 - Honorarios médicos, dentales y gastos hospitalarios</option>
                                <option value="D02" <?= old('uso_cfdi', $ticket['uso_cfdi'] ?? '') === 'D02' ? 'selected' : '' ?>>D02 - Gastos médicos por incapacidad o discapacidad</option>
                                <option value="D03" <?= old('uso_cfdi', $ticket['uso_cfdi'] ?? '') === 'D03' ? 'selected' : '' ?>>D03 - Gastos funerales</option>
                                <option value="D04" <?= old('uso_cfdi', $ticket['uso_cfdi'] ?? '') === 'D04' ? 'selected' : '' ?>>D04 - Donativos</option>
                                <option value="D05" <?= old('uso_cfdi', $ticket['uso_cfdi'] ?? '') === 'D05' ? 'selected' : '' ?>>D05 - Intereses reales efectivamente pagados por créditos hipotecarios</option>
                                <option value="D06" <?= old('uso_cfdi', $ticket['uso_cfdi'] ?? '') === 'D06' ? 'selected' : '' ?>>D06 - Aportaciones voluntarias al SAR</option>
                                <option value="D07" <?= old('uso_cfdi', $ticket['uso_cfdi'] ?? '') === 'D07' ? 'selected' : '' ?>>D07 - Primas por seguros de gastos médicos</option>
                                <option value="D08" <?= old('uso_cfdi', $ticket['uso_cfdi'] ?? '') === 'D08' ? 'selected' : '' ?>>D08 - Transporte escolar obligatorio</option>
                                <option value="D09" <?= old('uso_cfdi', $ticket['uso_cfdi'] ?? '') === 'D09' ? 'selected' : '' ?>>D09 - Depósitos en cuentas para el ahorro</option>
                                <option value="D10" <?= old('uso_cfdi', $ticket['uso_cfdi'] ?? '') === 'D10' ? 'selected' : '' ?>>D10 - Pagos por servicios educativos (colegiaturas)</option>
                                <option value="S01" <?= old('uso_cfdi', $ticket['uso_cfdi'] ?? '') === 'S01' ? 'selected' : '' ?>>S01 - Sin efectos fiscales</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correo fiscal (para recibir la factura)</label>
                        <input type="email" name="correo_fiscal" class="form-control" value="<?= safe_string(old('correo_fiscal', $ticket['correo_fiscal'] ?? $ticket['correo'] ?? '')) ?>">
                    </div>
                    <button type="submit" class="btn btn-dark w-100"><i class="bi bi-send"></i> Solicitar Factura</button>
                </form>
            </div>
        </div>
    </div>
</div>
