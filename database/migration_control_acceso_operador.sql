ALTER TABLE turno_produccion
  ADD COLUMN acceso_autorizado TINYINT(1) DEFAULT 0,
  ADD COLUMN acceso_autorizado_por INT DEFAULT NULL,
  ADD COLUMN acceso_autorizado_hasta DATETIME DEFAULT NULL;

INSERT IGNORE INTO turno_produccion (id_empleado, nombre_turno, hora_inicio, hora_fin, estatus)
SELECT e.id_empleado, 'matutino', '06:00:00', '14:00:00', 'activo'
FROM empleados e
INNER JOIN usuarios u ON u.id_empleado = e.id_empleado AND u.id_rol = 2
WHERE NOT EXISTS (
  SELECT 1 FROM turno_produccion tp WHERE tp.id_empleado = e.id_empleado
);
