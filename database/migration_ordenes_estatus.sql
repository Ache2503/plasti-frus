ALTER TABLE ordenes_cabecera
ADD COLUMN estatus VARCHAR(20) DEFAULT NULL
COMMENT 'pendiente, en_progreso, completada'
AFTER turno;

UPDATE ordenes_cabecera SET estatus = 'completada' WHERE cantidad_real_buenas IS NOT NULL AND cantidad_real_buenas > 0;
UPDATE ordenes_cabecera SET estatus = 'pendiente' WHERE estatus IS NULL;
