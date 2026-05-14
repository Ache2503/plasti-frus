-- Agregar roles Vendedor y Cliente
INSERT INTO roles (nombre, descripcion) VALUES 
('Vendedor', 'Ventas y atención a clientes'),
('Cliente', 'Cliente registrado con acceso al portal');

-- Agregar columna id_cliente a usuarios (opcional, para clientes registrados)
ALTER TABLE usuarios ADD COLUMN id_cliente INT NULL AFTER id_empleado,
ADD FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente);
