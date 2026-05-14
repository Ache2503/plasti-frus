CREATE TABLE IF NOT EXISTS horarios_operador (
    id_horario INT PRIMARY KEY AUTO_INCREMENT,
    id_empleado INT NOT NULL,
    turno VARCHAR(20) NOT NULL DEFAULT 'matutino',
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado),
    UNIQUE KEY uq_empleado (id_empleado)
);

INSERT IGNORE INTO horarios_operador (id_empleado, turno, hora_inicio, hora_fin)
SELECT e.id_empleado, 'matutino', '06:00:00', '14:00:00'
FROM empleados e
INNER JOIN usuarios u ON u.id_empleado = e.id_empleado AND u.id_rol = 2;

CREATE TABLE IF NOT EXISTS accesos_extraordinarios (
    id_acceso INT PRIMARY KEY AUTO_INCREMENT,
    id_empleado INT NOT NULL,
    autorizado_por INT NOT NULL,
    fecha_autorizacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    expiracion DATETIME NOT NULL,
    motivo VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado),
    FOREIGN KEY (autorizado_por) REFERENCES usuarios(id_usuario)
);
