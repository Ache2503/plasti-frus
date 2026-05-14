-- Migration: Módulo Vendedores
-- Ejecutar: mysql -u root -p fabrica_plasticos < database/migration_vendedores.sql

-- 1. Agregar id_vendedor a ventas (vendedor que registró la venta)
ALTER TABLE ventas
  ADD COLUMN id_vendedor INT NULL AFTER id_cliente,
  ADD FOREIGN KEY (id_vendedor) REFERENCES usuarios(id_usuario),
  ADD INDEX idx_id_vendedor (id_vendedor);

-- 2. Tabla de comisiones para vendedores
CREATE TABLE IF NOT EXISTS comisiones_vendedor (
    id_comision INT AUTO_INCREMENT PRIMARY KEY,
    id_vendedor INT NOT NULL,
    id_venta INT NOT NULL,
    monto_comision DECIMAL(10,2) NOT NULL DEFAULT 0,
    porcentaje_comision DECIMAL(5,2) NOT NULL DEFAULT 0,
    estatus VARCHAR(20) DEFAULT 'pendiente' COMMENT 'pendiente, pagada, cancelada',
    fecha_calculo DATE,
    fecha_pago DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_vendedor) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta) ON DELETE CASCADE,
    INDEX idx_id_vendedor (id_vendedor),
    INDEX idx_id_venta (id_venta),
    INDEX idx_estatus (estatus)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
