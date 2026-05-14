-- Migration: Mejoras módulo cliente
-- Ejecutar: mysql -u root -p fabrica_plasticos < database/migration_mejoras_cliente.sql

-- 1. Agregar precio_venta y stock a productos
ALTER TABLE productos
  ADD COLUMN precio_venta DECIMAL(10,2) DEFAULT 0 AFTER descripcion_comercial,
  ADD COLUMN stock_actual INT DEFAULT 0 AFTER precio_venta;

-- 2. Agregar domicilio de envío a clientes
ALTER TABLE clientes
  ADD COLUMN domicilio VARCHAR(255) DEFAULT NULL AFTER estado,
  ADD COLUMN codigo_postal VARCHAR(10) DEFAULT NULL AFTER domicilio,
  ADD COLUMN referencia_domicilio VARCHAR(255) DEFAULT NULL AFTER codigo_postal;

-- 3. Tabla pedidos (agrupa compras del carrito)
CREATE TABLE IF NOT EXISTS pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    folio VARCHAR(50) UNIQUE NOT NULL,
    total DECIMAL(10,2) NOT NULL DEFAULT 0,
    estatus VARCHAR(50) DEFAULT 'pendiente' COMMENT 'pendiente, procesando, completado, cancelado',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE,
    INDEX idx_id_cliente (id_cliente),
    INDEX idx_estatus (estatus)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Agregar id_pedido a ventas
ALTER TABLE ventas
  ADD COLUMN id_pedido INT DEFAULT NULL AFTER id_cliente,
  ADD FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido) ON DELETE SET NULL,
  ADD INDEX idx_id_pedido (id_pedido);

-- 5. Historial de estatus para pedidos (seguimiento)
CREATE TABLE IF NOT EXISTS pedidos_historial (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    estatus VARCHAR(50) NOT NULL,
    comentario TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido) ON DELETE CASCADE,
    INDEX idx_id_pedido (id_pedido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
