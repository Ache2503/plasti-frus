-- Agregar vendedor asignado a clientes
ALTER TABLE clientes ADD COLUMN id_vendedor INT NULL AFTER correo,
ADD FOREIGN KEY (id_vendedor) REFERENCES usuarios(id_usuario);

-- Solicitudes de factura de clientes
CREATE TABLE solicitudes_factura (
    id_solicitud INT PRIMARY KEY AUTO_INCREMENT,
    id_cliente INT NOT NULL,
    id_venta INT NOT NULL,
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estatus VARCHAR(20) DEFAULT 'pendiente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta)
);
