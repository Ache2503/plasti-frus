-- Correcciones para trazabilidad de soporte y mensajeria interna.
-- Mantiene compatibilidad con tickets existentes.

ALTER TABLE tickets_soporte
    ADD COLUMN IF NOT EXISTS id_usuario_creador INT NULL AFTER id_cliente;

-- Ejecutar la FK despues de validar datos existentes:
-- ALTER TABLE tickets_soporte
--     ADD CONSTRAINT fk_tickets_soporte_usuario_creador
--     FOREIGN KEY (id_usuario_creador) REFERENCES usuarios(id_usuario) ON DELETE SET NULL;
