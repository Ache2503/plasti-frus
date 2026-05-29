INSERT INTO roles (id_rol, nombre, descripcion) VALUES
    (1, 'Administrador', 'Acceso administrativo completo'),
    (2, 'Operador', 'Operacion de produccion'),
    (3, 'Supervisor', 'Supervision de produccion y soporte'),
    (4, 'Vendedor', 'Ventas y atencion a clientes'),
    (5, 'Cliente', 'Cliente registrado con acceso al portal'),
    (6, 'Contador', 'Contabilidad y finanzas')
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    descripcion = VALUES(descripcion);

ALTER TABLE usuarios
    ADD COLUMN IF NOT EXISTS id_cliente INT NULL AFTER id_empleado;

INSERT INTO empleados (nombre, apellido_paterno, apellido_materno, puesto, departamento, telefono, correo, fecha_contratacion, estatus)
SELECT 'Administrador', 'Sistema', '', 'Administrador', 'Sistemas', '555-0001', 'admin@plastifrus.local', CURDATE(), 'activo'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE nombre_usuario = 'admin');

SET @admin_empleado_id := COALESCE(
    (SELECT id_empleado FROM usuarios WHERE nombre_usuario = 'admin' LIMIT 1),
    LAST_INSERT_ID()
);

INSERT IGNORE INTO usuarios (id_empleado, id_cliente, nombre_usuario, password_hash, id_rol, activo)
VALUES (@admin_empleado_id, NULL, 'admin', '$2y$10$8hllgdRgBjFS3kxSVnn11u9g1WlSEypRZcH6V6JtF2T1iGtGEGomq', 1, 1);

INSERT INTO empleados (nombre, apellido_paterno, apellido_materno, puesto, departamento, telefono, correo, fecha_contratacion, estatus)
SELECT 'Supervisor', 'Produccion', '', 'Supervisor', 'Produccion', '555-0002', 'supervisor@plastifrus.local', CURDATE(), 'activo'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE nombre_usuario = 'supervisor');

SET @supervisor_empleado_id := COALESCE(
    (SELECT id_empleado FROM usuarios WHERE nombre_usuario = 'supervisor' LIMIT 1),
    LAST_INSERT_ID()
);

INSERT IGNORE INTO usuarios (id_empleado, id_cliente, nombre_usuario, password_hash, id_rol, activo)
VALUES (@supervisor_empleado_id, NULL, 'supervisor', '$2y$10$8hllgdRgBjFS3kxSVnn11u9g1WlSEypRZcH6V6JtF2T1iGtGEGomq', 3, 1);

INSERT INTO empleados (nombre, apellido_paterno, apellido_materno, puesto, departamento, telefono, correo, fecha_contratacion, estatus)
SELECT 'Contador', 'General', '', 'Contador', 'Contabilidad', '555-0003', 'contador@plastifrus.local', CURDATE(), 'activo'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE nombre_usuario = 'contador');

SET @contador_empleado_id := COALESCE(
    (SELECT id_empleado FROM usuarios WHERE nombre_usuario = 'contador' LIMIT 1),
    LAST_INSERT_ID()
);

INSERT IGNORE INTO usuarios (id_empleado, id_cliente, nombre_usuario, password_hash, id_rol, activo)
VALUES (@contador_empleado_id, NULL, 'contador', '$2y$10$8hllgdRgBjFS3kxSVnn11u9g1WlSEypRZcH6V6JtF2T1iGtGEGomq', 6, 1);
