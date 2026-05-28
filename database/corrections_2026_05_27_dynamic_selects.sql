-- Correcciones para reemplazar capturas manuales por selects dinamicos.
-- Mantiene columnas texto existentes para compatibilidad y agrega IDs normalizados.

ALTER TABLE mantenimientos_maquinas
    ADD COLUMN IF NOT EXISTS id_tecnico_responsable INT NULL AFTER tecnico_responsable;

ALTER TABLE plan_mantenimiento
    ADD COLUMN IF NOT EXISTS id_tecnico_responsable INT NULL AFTER tecnico_responsable;

ALTER TABLE bitacora_paros
    ADD COLUMN IF NOT EXISTS id_operador INT NULL AFTER operador;

ALTER TABLE kardex_materiales
    ADD COLUMN IF NOT EXISTS id_operador INT NULL AFTER operador;

-- Ejecutar estas FK despues de validar que los valores existentes sean consistentes:
-- ALTER TABLE mantenimientos_maquinas
--     ADD CONSTRAINT fk_mant_maquinas_tecnico
--     FOREIGN KEY (id_tecnico_responsable) REFERENCES usuarios(id_usuario);
-- ALTER TABLE plan_mantenimiento
--     ADD CONSTRAINT fk_plan_mant_tecnico
--     FOREIGN KEY (id_tecnico_responsable) REFERENCES usuarios(id_usuario);
-- ALTER TABLE bitacora_paros
--     ADD CONSTRAINT fk_bitacora_paros_operador
--     FOREIGN KEY (id_operador) REFERENCES usuarios(id_usuario);
-- ALTER TABLE kardex_materiales
--     ADD CONSTRAINT fk_kardex_materiales_operador
--     FOREIGN KEY (id_operador) REFERENCES usuarios(id_usuario);
