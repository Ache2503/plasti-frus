-- Correcciones detectadas en auditoria tecnica.
-- Ejecutar despues de database/schema.sql y migraciones historicas.
-- Compatible con MariaDB/MySQL que soporten ADD COLUMN IF NOT EXISTS.

ALTER TABLE clientes
    ADD COLUMN IF NOT EXISTS activo TINYINT(1) NOT NULL DEFAULT 1 AFTER sector;

ALTER TABLE clientes
    ADD COLUMN IF NOT EXISTS id_vendedor INT NULL AFTER correo;

ALTER TABLE productos
    ADD COLUMN IF NOT EXISTS activo TINYINT(1) NOT NULL DEFAULT 1 AFTER publicar_web;

ALTER TABLE maquinas
    ADD COLUMN IF NOT EXISTS estado ENUM('operando','setup','detenida','apagada','mantenimiento')
    DEFAULT 'apagada' AFTER estatus;

ALTER TABLE maquinas
    ADD COLUMN IF NOT EXISTS seccion VARCHAR(100) NULL AFTER estado;

ALTER TABLE ordenes_cabecera
    ADD COLUMN IF NOT EXISTS estatus VARCHAR(20) DEFAULT 'pendiente' AFTER turno;

UPDATE clientes SET activo = 1 WHERE activo IS NULL;
UPDATE productos SET activo = 1 WHERE activo IS NULL;
UPDATE maquinas SET estado = 'apagada' WHERE estado IS NULL;
UPDATE ordenes_cabecera
SET estatus = CASE
    WHEN cantidad_real_buenas IS NOT NULL AND cantidad_real_buenas > 0 THEN 'completada'
    ELSE 'pendiente'
END
WHERE estatus IS NULL;

-- Agregar esta FK manualmente solo despues de validar que no existan valores huerfanos:
-- ALTER TABLE clientes
--     ADD CONSTRAINT fk_clientes_vendedor
--     FOREIGN KEY (id_vendedor) REFERENCES usuarios(id_usuario);
